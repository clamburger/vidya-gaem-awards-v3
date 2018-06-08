<?php
namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Irc\Bot;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitchChatCommand extends ContainerAwareCommand
{
    const CHANNEL = '#clamburger_';

    private $em;

    /** @var OutputInterface */
    private $output;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:irc-monitor')
            ->setDescription('Monitors Twitch chat to gauge audience sentiment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $output->writeln('Starting IRC client...');

        $twitchUser = $this->getContainer()->getParameter('twitch_user');
        $twitchPassword = $this->getContainer()->getParameter('twitch_password');

        if (!$twitchUser || !$twitchPassword) {
            throw new \Exception('Twitch credentials not defined.');
        }

        $bot = new Bot($twitchUser, 'irc.chat.twitch.tv');
        $bot->setServerPassword($twitchPassword);

        $bot->on('connected', function ($e, Bot $bot) use ($output) {
            ('Connected');
        });

        $bot->on('message', function ($e, Bot $bot) {
//            dump('Message');
            dump($e->raw);
        });

        $bot->on('welcome', function ($e, Bot $bot) {
            dump('Welcome!');
            $bot->join(self::CHANNEL);
            $bot->send('PRIVMSG', self::CHANNEL, '/v/3 bot connected.');
        });

        $bot->connect();
    }

    public function messageReceived(array $message, WriteStream $write, ConnectionInterface $connection, LoggerInterface $logger)
    {
        $this->output->writeln('Message received.');
        dump($message);
    }
}
