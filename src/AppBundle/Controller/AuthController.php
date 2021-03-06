<?php
namespace AppBundle\Controller;

use Ehesp\SteamLogin\SteamLogin;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;

class AuthController extends Controller
{
    public function loginAction(RouterInterface $router, Request $request, SessionInterface $session)
    {
        $session->set('_security.main.target_path', $request->query->get('redirect'));

        $returnLink = $router->generate(
            'login_check',
            [],
            UrlGenerator::ABSOLUTE_URL
        );

        $steam = new SteamLogin();
        return new RedirectResponse($steam->url($returnLink));
    }
}
