<?php
namespace AppBundle\Service;

use Doctrine\ORM\EntityManagerInterface;

class SentimentAnalysisService
{
    private $config;

    public function __construct(ConfigService $config)
    {
        $this->config = $config;
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
}
