<?php

namespace AppBundle\Entity;

class ChatMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     */
    private $sentiment;


    public function getId(): int
    {
        return $this->id;
    }

    public function setDate(\DateTime $date): ChatMessage
    {
        $this->date = $date;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setUser(string $user): ChatMessage
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setSentiment(int $sentiment): ChatMessage
    {
        $this->sentiment = $sentiment;
        return $this;
    }

    public function getSentiment(): ?int
    {
        return $this->sentiment;
    }
}
