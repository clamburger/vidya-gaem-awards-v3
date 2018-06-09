<?php
namespace AppBundle\Controller;

use AppBundle\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\RouterInterface;

class StaticController extends Controller
{
    public function indexAction(RouterInterface $router, ConfigService $configService)
    {
        $defaultPage = $configService->getConfig()->getDefaultPage();
        $defaultRoute = $router->getRouteCollection()->get($defaultPage);

        return $this->forward($defaultRoute->getDefault('_controller'), $defaultRoute->getDefaults());
    }
}
