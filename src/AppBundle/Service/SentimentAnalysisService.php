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
            if (preg_match('{\b' . preg_quote($rule[0]) . '\b}', $text)) {
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
            ->select('SUM( TIMESTAMPDIFF(SECOND, :date, cm.date) * cm.sentiment) / SUM( TIMESTAMPDIFF(SECOND, :date, cm.date) )')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', new \DateTime('-120 seconds'))
            ->andWhere('cm.sentiment IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$sentiment;
    }

    /**
     * @return int Returns an int representing the current intensity of chat.
     */
    public function getCurrentIntensity(): int
    {
        $intensity = (int)$this->em->createQueryBuilder()
            ->select('SUM( TIMESTAMPDIFF(SECOND, :date, cm.date) )')
            ->from(ChatMessage::class, 'cm')
            ->where('cm.date >= :date')
            ->setParameter('date', new \DateTime('-120 seconds'))
            ->andWhere('cm.sentiment IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        return (int)(round($intensity / 120));
    }

    public function getLastMesage(): string
    {
        $message = (string)$this->em->createQueryBuilder()
            ->select('cm.message')
            ->from(ChatMessage::class, 'cm')
            ->orderBy('cm.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return $message;
    }
}
