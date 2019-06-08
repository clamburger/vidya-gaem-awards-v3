<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Action;
use AppBundle\Entity\ChatMessage;
use AppBundle\Service\AuditService;
use AppBundle\Service\ConfigService;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use fXmlRpc\Client;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Exception\TransportException;
use fXmlRpc\Transport\HttpAdapterTransport;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Supervisor\Connector\XmlRpc;
use Supervisor\Supervisor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatMonitoringPanelController extends Controller
{
    const SUPERVISOR_PROCESS_NAME_TWITCH = 'vga-twitch-monitor';
    const SUPERVISOR_PROCESS_NAME_YOUTUBE = 'vga-youtube-monitor';
    const SUPERVISOR_PROCESS_NAME_NODE = 'vga-node-monitor';

    private function getSupervisor()
    {
        $guzzleClient = new \GuzzleHttp\Client(['auth' => [
            $this->getParameter('supervisor_user'), $this->getParameter('supervisor_password')
        ]]);

        try {
            $client = new Client(
                'http://127.0.0.1:9001/RPC2',
                new HttpAdapterTransport(
                    new GuzzleMessageFactory(),
                    new GuzzleAdapter($guzzleClient)
                )
            );

            $connector = new XmlRpc($client);
            $supervisor = new Supervisor($connector);
            $supervisor->getAllProcessInfo();
        } catch (TransportException $e) {
            return null;
        }

        return $supervisor;
    }

    public function indexAction(EntityManagerInterface $em, ConfigService $configService)
    {
        $twitch = $youtube = $node = [];

        $supervisor = $this->getSupervisor();
        if ($supervisor) {
            $twitch['supervisor'] = $supervisor->getProcessInfo(self::SUPERVISOR_PROCESS_NAME_TWITCH);
            $twitch['running'] = $twitch['supervisor']['statename'] === 'RUNNING';

            $youtube['supervisor'] = $supervisor->getProcessInfo(self::SUPERVISOR_PROCESS_NAME_YOUTUBE);
            $youtube['running'] = $youtube['supervisor']['statename'] === 'RUNNING';

            $node['supervisor'] = $supervisor->getProcessInfo(self::SUPERVISOR_PROCESS_NAME_NODE);
            $node['running'] = $node['supervisor']['statename'] === 'RUNNING';
        }

        $messages = $em->createQueryBuilder()
            ->select('cm')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', new \DateTime('-1 hour'))
            ->setMaxResults(20)
            ->orderBy('cm.date', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('chatMonitoringPanel.html.twig', [
            'title' => 'Chat Monitoring Panel',
            'supervisor' => $supervisor,
            'twitch' => $twitch,
            'youtube' => $youtube,
            'youtubeVideoId' => $configService->getConfig()->getYoutubeStreamId(),
            'node' => $node,
            'messages' => $messages
        ]);
    }

    public function updateSupervisorStateAction(Request $request, SessionInterface $session, AuditService $auditService, ConfigService $configService, EntityManagerInterface $em)
    {
        $post = $request->request;

        $supervisor = $this->getSupervisor();

        /** @var Session $session */

        $processName = 'vga-' . $post->get('service') . '-monitor';

        if ($post->get('youtubeId')) {
            $currentState = $supervisor->getProcessInfo('vga-youtube-monitor')['statename'];
            if ($currentState !== 'RUNNING') {
                $config = $configService->getConfig();
                $config->setYoutubeStreamId($post->get('youtubeId'));
                $em->persist($config);
                $em->flush();
            }
        }

        if ($post->get('action') === 'start') {
            try {
                $supervisor->startProcess($processName, true);
                $session->getFlashBag()->add('success', 'Start command sent.');
                $auditService->add(new Action('supervisor-started'));
            } catch (FaultException $e) {
                $session->getFlashBag()->add('error', 'Unable to start process: ' . $e->getMessage());
            }
        } elseif ($post->get('action') === 'stop') {
            try {
                $supervisor->stopProcess($processName, true);
                $session->getFlashBag()->add('success', 'Stop command sent.');
                $auditService->add(new Action('supervisor-stopped'));
            } catch (FaultException $e) {
                $session->getFlashBag()->add('error', 'Unable to stop process: ' . $e->getMessage());
            }
        } else {
            $session->getFlashBag()->add('error', 'Invalid command.');
        }

        return $this->redirectToRoute('chatMonitoringPanel');
    }
}
