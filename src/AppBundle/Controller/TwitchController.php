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

class TwitchController extends Controller
{
    public function indexAction(EntityManagerInterface $em)
    {
        return $this->render('twitchChat.html.twig', [
            'title' => 'Twitch Chat'
        ]);
    }
}
