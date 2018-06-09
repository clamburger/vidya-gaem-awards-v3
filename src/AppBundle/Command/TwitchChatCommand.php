<?php
namespace AppBundle\Command;

use AppBundle\Entity\ChatMessage;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Irc\Bot;
use Irc\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitchChatCommand extends ContainerAwareCommand
{
    const CHANNEL = '#vidyagaemawards';

    private $em;
    private $sas;

    /** @var OutputInterface */
    private $output;

    public function __construct(EntityManagerInterface $em, SentimentAnalysisService $sas)
    {
        $this->em = $em;
        $this->sas = $sas;
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
            $output->writeln('Connected.');
        });

        $bot->on('welcome', function ($e, Bot $bot) use ($output) {
            $output->writeln('Welcome message received.');
            $bot->join(self::CHANNEL);
        });

        $bot->on('join:' . $twitchUser, function ($e, Bot $bot) use ($output) {
            $output->writeln('Channel ' . self::CHANNEL . ' joined.');
            $bot->chat(self::CHANNEL, 'Reactor online. Sensors online. Weapons online. All systems nominal.');
        });

        $bot->on('message', [$this, 'messageReceived']);

        $bot->connect();
    }

    public function messageReceived($e, Bot $bot)
    {
        $this->output->writeln('    ' . $e->raw);

        if ($e->message->args[0] !== self::CHANNEL || $e->message->command !== Bot::CMD_PRIVMSG) {
            dump('not sent');
            return;
        }

        if (!isset($e->message->args[1])) {
            dump('Unexpected message');
            dump($e->raw);
            dump($e->message);
            return;
        }

        $sender = $e->message->nick;
        $text = $e->message->args[1];

        $chatMessage = new ChatMessage();
        $chatMessage
            ->setDate(\DateTime::createFromFormat('U', $e->time))
            ->setUser($sender)
            ->setMessage($text)
            ->setSentiment($this->sas->getSentimentForText($text));

        $this->em->persist($chatMessage);
        $this->em->flush();
    }
}
