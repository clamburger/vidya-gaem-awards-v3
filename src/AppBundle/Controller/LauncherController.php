<?php
namespace AppBundle\Controller;

use AppBundle\Service\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LauncherController extends Controller
{
    public function countdownAction(ConfigService $configService, EntityManagerInterface $em)
    {
        $streamDate = $configService->getConfig()->getStreamTime();

        $timezones = [
            'Honolulu' => 'Pacific/Honolulu',
            'Anchorage' => 'America/Anchorage',
            'Los Angeles (PST)' => 'America/Los_Angeles',
            'Denver (MST)' => 'America/Denver',
            'Chicago (CST)' => 'America/Chicago',
            '4chan Time (EST)' => 'America/New_York',
            'Rio de Janeiro' => 'America/Sao_Paulo',
            'London (GMT)' => 'Europe/London',
            'Paris (CET)' => 'Europe/Paris',
            'Athens (EET)' => 'Europe/Athens',
            'Moscow' => 'Europe/Moscow',
            'Singapore' => 'Asia/Singapore',
            'Japan Time' => 'Asia/Tokyo',
            'Brisbane (AEST)' => 'Australia/Brisbane',
            'Sydney (AEDT)' => 'Australia/Sydney',
            'Auckland' => 'Pacific/Auckland',
        ];

        $otherTimezonesLink = sprintf(
            'https://www.timeanddate.com/worldclock/fixedtime.html?msg=2017+Vidya+Gaem+Awards&iso=%s&p1=179',
            $streamDate ? $streamDate->format("Y-m-d\TH:i:s") : ''
        );

        // Fake ads
        $ad1 = $ad2 = false;

        return $this->render('countdown.html.twig', [
            'streamDate' => $streamDate,
            'timezones' => $timezones,
            'otherTimezonesLink' => $otherTimezonesLink,
            'ad1' => $ad1,
            'ad2' => $ad2,
        ]);
    }

    public function streamAction(ConfigService $configService)
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        $streamDate = $configService->getConfig()->getStreamTime();
        $showCountdown = ($streamDate > new \DateTime());

        return $this->render('stream.html.twig', [
            'streamDate' => $streamDate,
            'countdown' => $showCountdown
        ]);
    }

    public function finishedAction()
    {
        return $this->render('finished.html.twig');
    }
}
