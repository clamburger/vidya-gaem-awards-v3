<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ChatMessage;
use AppBundle\Service\ConfigService;
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

    public function getSentimentAction(EntityManagerInterface $em)
    {
        // To determine the sentiment, we use a linear weighted rolling average of the past two minutes.
        $sentiment = $em->createQueryBuilder()
            ->select('AVG((120 - TIMESTAMPDIFF(SECOND, cm.date, CURRENT_TIMESTAMP()) * cm.sentiment))')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', '-120 seconds')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'sentiment' => (int)$sentiment,
        ]);
    }
}
