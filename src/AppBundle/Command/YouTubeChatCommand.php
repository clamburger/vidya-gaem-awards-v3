<?php
namespace AppBundle\Command;

use AppBundle\Entity\ChatMessage;
use AppBundle\Service\ConfigService;
use AppBundle\Service\SentimentAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Irc\Bot;
use Irc\Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class YouTubeChatCommand extends ContainerAwareCommand
{
    const CHANNEL = '#vidyagaemawards';

    private $em;
    private $sas;
    private $client;
    private $configService;

    /** @var OutputInterface */
    private $output;

    public function __construct(EntityManagerInterface $em, SentimentAnalysisService $sas, ConfigService $configService)
    {
        $this->em = $em;
        $this->sas = $sas;
        $this->configService = $configService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:youtube-monitor')
            ->setDescription('Monitors YouTube chat to gauge audience sentiment.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $this->client = new \Google_Client();
        $this->client->setClientId($this->getContainer()->getParameter('google_client_id'));
        $this->client->setClientSecret($this->getContainer()->getParameter('google_client_secret'));
        $this->client->addScope(\Google_Service_YouTube::YOUTUBE_READONLY);
        $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);

        $refreshToken = $this->getContainer()->getParameter('google_refresh_token');
        $accessToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        $this->client->setAccessToken($accessToken);

        $youtube = new \Google_Service_YouTube($this->client);

        $id = $this->configService->getConfig()->getYoutubeStreamId();
        if (!$id) {
            $output->writeln('No YouTube video ID specified in config.');
            exit(6);
        }

        $broadcasts = $youtube->liveBroadcasts->listLiveBroadcasts(
            'id,snippet,contentDetails,status',
            ['id' => $id]
        );

        if (empty($broadcasts->getItems())) {
            $output->writeln('Couldn\'t find a broadcast with the specified ID.');
            exit(7);
        }

        $chatId = $broadcasts->getItems()[0]->snippet->liveChatId;

        if ($chatId === null) {
            $output->writeln('Couldn\'t find a live chat for that broadcast (it may have already ended)');
            exit(8);
        }

        $mostRecentMessageTime = new \DateTime(file_get_contents(__DIR__ . '/lastYouTubeMessage.txt'));

        while (true) {
            $data = $youtube->liveChatMessages->listLiveChatMessages(
                $chatId,
                'id,snippet,authorDetails'
            );

            $waitingTime = ceil($data->getPollingIntervalMillis() / 1000) + 1;
            sleep(min($waitingTime, 3));

            $messages = $data->getItems();
            /** @var \Google_Service_YouTube_LiveChatMessage $message */
            foreach ($messages as $message) {
                $date = $message->getSnippet()->getPublishedAt();

                if (new \DateTime($date) <= $mostRecentMessageTime) {
                    continue;
                }

                $text = $message->getSnippet()->getDisplayMessage();
                $author = $message->getAuthorDetails()->getDisplayName();

                $chatMessage = new ChatMessage();
                $chatMessage
                    ->setDate(new \DateTime($date))
                    ->setUser($author)
                    ->setMessage($text)
                    ->setSentiment($this->sas->getSentimentForText($text))
                    ->setSource('YouTube');

                $this->em->persist($chatMessage);
                $this->em->flush();

                $output->writeln($author . ': ' . $text);

                $mostRecentMessageTime = new \DateTime($date);
                file_put_contents(__DIR__ . '/lastYouTubeMessage.txt', $date);
            }
        }
    }
}
