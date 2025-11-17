<?php

namespace App\Entity\Behavior;

use App\Entity\User;
use App\Repository\Behavior\UserLimitationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * INVISIBLE AUTOMATIC LIMITATIONS
 * Applied silently based on profiteur score
 * Users never see these restrictions, just consequences
 *
 * @ORM\Entity(repositoryClass=UserLimitationRepository::class)
 * @ORM\Table(name="user_limitations")
 */
class UserLimitation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $limitationType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $limitationDetails;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="datetime")
     */
    private $appliedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $reason;

    public function __construct()
    {
        $this->appliedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLimitationType(): ?string
    {
        return $this->limitationType;
    }

    public function setLimitationType(string $limitationType): self
    {
        $this->limitationType = $limitationType;
        return $this;
    }

    public function getLimitationDetails(): ?string
    {
        return $this->limitationDetails;
    }

    public function setLimitationDetails(?string $limitationDetails): self
    {
        $this->limitationDetails = $limitationDetails;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getAppliedAt(): ?\DateTimeInterface
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(\DateTimeInterface $appliedAt): self
    {
        $this->appliedAt = $appliedAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }
}
