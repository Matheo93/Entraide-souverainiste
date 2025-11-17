<?php

namespace App\Entity\Behavior;

use App\Entity\Conversations\Conversation;
use App\Entity\User;
use App\Repository\Behavior\PointsTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * AUDIT TRAIL for invisible points system
 * Records all point distributions for transparency (admin only)
 *
 * @ORM\Entity(repositoryClass=PointsTransactionRepository::class)
 * @ORM\Table(name="points_transactions")
 */
class PointsTransaction
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
     * @ORM\Column(type="integer")
     */
    private $pointsChange;

    /**
     * @ORM\Column(type="integer")
     */
    private $balanceAfter;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $transactionType;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $relatedConversation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getPointsChange(): ?int
    {
        return $this->pointsChange;
    }

    public function setPointsChange(int $pointsChange): self
    {
        $this->pointsChange = $pointsChange;
        return $this;
    }

    public function getBalanceAfter(): ?int
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(int $balanceAfter): self
    {
        $this->balanceAfter = $balanceAfter;
        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType): self
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function getRelatedConversation(): ?Conversation
    {
        return $this->relatedConversation;
    }

    public function setRelatedConversation(?Conversation $relatedConversation): self
    {
        $this->relatedConversation = $relatedConversation;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
