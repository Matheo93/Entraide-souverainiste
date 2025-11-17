<?php

namespace App\Entity;

use App\Repository\ContactFormRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContactFormRepository::class)
 */
class ContactForm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $regiseredAt;


    public function __construct()
    {
        $date = new \DateTimeImmutable();
        $date->setTimezone(new \DateTimeZone('Europe/Paris'));

        $this->setRegiseredAt($date);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getRegiseredAt(): ?\DateTimeImmutable
    {
        return $this->regiseredAt;
    }

    public function setRegiseredAt(\DateTimeImmutable $regiseredAt): self
    {
        $this->regiseredAt = $regiseredAt;

        return $this;
    }
}
