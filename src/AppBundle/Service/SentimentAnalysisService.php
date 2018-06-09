<?php
namespace AppBundle\Service;

use AppBundle\Entity\ChatMessage;
use Doctrine\ORM\EntityManagerInterface;

class SentimentAnalysisService
{
    private $config;
    private $em;

    public function __construct(ConfigService $config, EntityManagerInterface $em)
    {
        $this->config = $config;
        $this->em = $em;
    }

    public function getSentimentForText(string $text)
    {
        $text = trim(strtolower($text));

        $rules = $this->config->getConfig()->getSentimentRules();

        foreach ($rules['exact'] as $rule) {
            if ($text === $rule[0]) {
                return $rule[1];
            }
        }
        foreach ($rules['contains'] as $rule) {
            if (preg_match('/\b' . $rule[0] . '\b/', $text)) {
                return $rule[1];
            }
        }
        foreach ($rules['regex'] as $rule) {
            if (preg_match('/' . $rule[0] . '/', $text)) {
                return $rule[1];
            }
        }

        return null;
    }

    public function getCurrentSentiment(): int
    {
        // To determine the sentiment, we use a linear weighted rolling average of the past two minutes.
        $sentiment = $this->em->createQueryBuilder()
            ->select('AVG((TIMESTAMPDIFF(SECOND, :date, cm.date)) * cm.sentiment)')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', new \DateTime('-120 seconds'))
            ->getQuery()
            ->getSingleScalarResult();

        $sentiment /= 120;
        return (int)$sentiment;
    }
}
