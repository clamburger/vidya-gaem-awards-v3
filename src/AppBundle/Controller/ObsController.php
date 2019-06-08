<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ChatMessage;
use AppBundle\Service\ConfigService;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;

class ObsController extends Controller
{
    public function indexAction(RouterInterface $router, ConfigService $configService)
    {
        $defaultPage = $configService->getConfig()->getDefaultPage();
        $defaultRoute = $router->getRouteCollection()->get($defaultPage);

        return $this->forward($defaultRoute->getDefault('_controller'), $defaultRoute->getDefaults());
    }

    public function overlayAction(Environment $twig, Request $request) {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        $overlay = $request->query->get('overlay');
        if (!$twig->getLoader()->exists('overlays/' . $overlay . '.html.twig')) {
            return new Response('Invalid overlay selected');
        }

        return $this->render('overlays/' . $overlay . '.html.twig');
    }

    public function overlayFullAction(Environment $twig, Request $request) {
        $overlay = $request->query->get('overlay');
        if (!$twig->getLoader()->exists('overlays/' . $overlay . '.html.twig')) {
            return new Response('Invalid overlay selected');
        }

        return $this->render('obsOverlayFull.html.twig', [
            'overlay' => $overlay
        ]);
    }

    public function getSentimentAction(SentimentAnalysisService $sentimentService)
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        return $this->json([
            'sentiment' => $sentimentService->getCurrentSentiment(),
            'intensity' => $sentimentService->getCurrentIntensity(),
        ]);
    }
}
