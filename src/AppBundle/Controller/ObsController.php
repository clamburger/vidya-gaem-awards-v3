<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ChatMessage;
use AppBundle\Service\ConfigService;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;

class ObsController extends Controller
{
    public function indexAction(RouterInterface $router, ConfigService $configService)
    {
        $defaultPage = $configService->getConfig()->getDefaultPage();
        $defaultRoute = $router->getRouteCollection()->get($defaultPage);

        return $this->forward($defaultRoute->getDefault('_controller'), $defaultRoute->getDefaults());
    }

    public function overlayAction()
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        return $this->render('obsOverlay.html.twig');
    }

    public function getSentimentAction(SentimentAnalysisService $sentimentService)
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        return $this->json([
            'sentiment' => $sentimentService->getCurrentSentiment(),
        ]);
    }
}
