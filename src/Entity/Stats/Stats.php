<?php

namespace App\Entity\Stats;

use App\Entity\User;
use App\Entity\Stats\StatsType;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Stats\StatsRepository;

/**
 * @ORM\Entity(repositoryClass=StatsRepository::class)
 */
class Stats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=StatsType::class, inversedBy="stats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="json")
     */
    private $content = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $registered_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="stats")
     */
    private $user;

    public function __construct(){
        $this->registered_at = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?StatsType
    {
        return $this->type;
    }

    public function setType(?StatsType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registered_at;
    }

    public function setRegisteredAt(\DateTimeInterface $registered_at): self
    {
        $this->registered_at = $registered_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
