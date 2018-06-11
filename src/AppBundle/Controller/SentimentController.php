<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Config;
use AppBundle\Service\AuditService;
use AppBundle\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Action;
use AppBundle\Entity\TableHistory;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class SentimentController extends Controller
{
    public function indexAction(EntityManagerInterface $em, ConfigService $config)
    {
        $sentiment = $config->getConfig()->getSentimentRules();
        $sentimentText = [];

        foreach ($sentiment as $id => $rules) {
            $text = '';
            foreach ($rules as $rule) {
                $text .= $rule[0] . '|' . $rule[1] . "\n";
            }

            $sentimentText[$id] = $text;
        }

        return $this->render('sentimentAnalysis.html.twig', [
            'title' => 'Sentiment Analysis',
            'sentiment' => $sentimentText,
            'config' => $config->getConfig()
        ]);
    }

    public function postAction(EntityManagerInterface $em, ConfigService $configService, Request $request, AuditService $auditService)
    {
        $config = $configService->getConfig();
        
        if ($config->isReadOnly()) {
            $this->addFlash('error', 'The site is currently in read-only mode. No changes can be made.');
            return $this->redirectToRoute('sentimentAnalysis');
        }

        $post = $request->request;

        $rules = [
            'exact' => $post->get('exact'),
            'contains' => $post->get('contains'),
            'regex' => $post->get('regex')
        ];

        foreach ($rules as $id => $text) {
            $rules[$id] = $this->parseRuleTextblock($id, $text,$errors);
        }

        if (empty($errors)) {
            $this->addFlash('success', 'Sentiment rules successfully saved.');
        } else {
            $this->addFlash('error', 'Some of your rules had errors and have not been saved.');
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
        }

        $config->setSentimentRules($rules);
        $em->persist($config);
        $em->flush();

        $auditService->add(
            new Action('sentiment-rules-updated', 1),
            new TableHistory(Config::class, 1, $post->all())
        );

        return $this->redirectToRoute('sentimentAnalysis');
    }

    private function parseRuleTextblock(string $id, string $text, &$errors = [])
    {
        $rules = [];

        $lines = explode("\n", $text);

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            $rule = explode("|", $line);
            if (count($rule) !== 2 || !preg_match('/^-?\d+$/', $rule[1])) {
                $errors[] = $id . ': Invalid syntax on line ' . ($lineNumber + 1);
                continue;
            }

            $number = (int)$rule[1];
            if ($number < -100 || $number > 100) {
                $errors[] = $id . ': Sentiment must be between -100 and 100 on line ' . ($lineNumber + 1);
                continue;
            }

            $rules[] = [trim(strtolower($rule[0])), $number];
        }

        return $rules;
    }

    public function voteAction(Request $request)
    {
        return $this->render('votingPopout.html.twig', [
            'popout' => $request->query->get('popout', false)
        ]);
    }
}
