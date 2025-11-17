<?php

namespace App\Entity\Announces;

use App\Repository\Announces\AnnouncesRequestsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnnouncesRequestsRepository::class)
 */
class AnnouncesRequests
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Announces::class, inversedBy="announcesRequests")
     */
    private $announce;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $registeredAt;

    public function __construct(){
        $date = new \DateTimeImmutable();
        $this->setRegisteredAt($date);

        $defaultData = [];
        $this->setData($defaultData);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnounce(): ?Announces
    {
        return $this->announce;
    }

    public function setAnnounce(?Announces $announce): self
    {
        $this->announce = $announce;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): self
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }
}
