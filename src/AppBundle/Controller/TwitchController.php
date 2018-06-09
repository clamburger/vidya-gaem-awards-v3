<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Action;
use AppBundle\Entity\ChatMessage;
use AppBundle\Service\AuditService;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use fXmlRpc\Client;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Transport\HttpAdapterTransport;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Supervisor\Connector\XmlRpc;
use Supervisor\Supervisor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TwitchController extends Controller
{
    const SUPERVISOR_PROCESS_NAME = 'vga-twitch-monitor';

    private function getSupervisor()
    {
        $guzzleClient = new \GuzzleHttp\Client(['auth' => [
            $this->getParameter('supervisor_user'), $this->getParameter('supervisor_password')
        ]]);

        $client = new Client(
            'http://127.0.0.1:9001/RPC2',
            new HttpAdapterTransport(
                new GuzzleMessageFactory(),
                new GuzzleAdapter($guzzleClient)
            )
        );

        $connector = new XmlRpc($client);
        return new Supervisor($connector);
    }

    public function indexAction(EntityManagerInterface $em, SentimentAnalysisService $sentimentService)
    {
        $supervisorInfo = $this->getSupervisor()->getProcessInfo(self::SUPERVISOR_PROCESS_NAME);
        $running = $supervisorInfo['statename'] === 'RUNNING';

        $messages = $em->createQueryBuilder()
            ->select('cm')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', new \DateTime('-1 hour'))
            ->setMaxResults(20)
            ->orderBy('cm.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('twitchChat.html.twig', [
            'title' => 'Twitch Chat',
            'supervisorInfo' => $supervisorInfo,
            'running' => $running,
            'messages' => $messages,
            'currentSentiment' => $sentimentService->getCurrentSentiment()
        ]);
    }

    public function updateSupervisorStateAction(Request $request, SessionInterface $session, AuditService $auditService)
    {
        $post = $request->request;

        $supervisor = $this->getSupervisor();

        /** @var Session $session */

        if ($post->get('action') === 'start') {
            try {
                $supervisor->startProcess('vga-twitch-monitor', true);
                $session->getFlashBag()->add('success', 'Start command sent.');
                $auditService->add(new Action('supervisor-started'));
            } catch (FaultException $e) {
                $session->getFlashBag()->add('error', 'Unable to start process: ' . $e->getMessage());
            }
        } elseif ($post->get('action') === 'stop') {
            try {
                $supervisor->stopProcess('vga-twitch-monitor', true);
                $session->getFlashBag()->add('success', 'Stop command sent.');
                $auditService->add(new Action('supervisor-stopped'));
            } catch (FaultException $e) {
                $session->getFlashBag()->add('error', 'Unable to stop process: ' . $e->getMessage());
            }
        } else {
            $session->getFlashBag()->add('error', 'Invalid command.');
        }

        return $this->redirectToRoute('twitchChat');
    }
}
