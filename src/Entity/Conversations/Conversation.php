<?php

namespace App\Entity\Conversations;

use App\Entity\Announces\Announces;
use App\Entity\User;
use App\Repository\Conversations\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 * @ORM\Table(name="conversations")
 */
class Conversation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Announces::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $announce;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $userOffrant;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $userDemandeur;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status = 'EN_ATTENTE_REPONSE';

    /**
     * @ORM\Column(type="integer")
     */
    private $messagesCount = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastMessageAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $closedByUser;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $closureType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pointsDistributed = false;

    /**
     * @ORM\OneToMany(targetEntity=ConversationMessage::class, mappedBy="conversation", orphanRemoval=true)
     * @ORM\OrderBy({"sentAt" = "ASC"})
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTime();
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

    public function getUserOffrant(): ?User
    {
        return $this->userOffrant;
    }

    public function setUserOffrant(?User $userOffrant): self
    {
        $this->userOffrant = $userOffrant;
        return $this;
    }

    public function getUserDemandeur(): ?User
    {
        return $this->userDemandeur;
    }

    public function setUserDemandeur(?User $userDemandeur): self
    {
        $this->userDemandeur = $userDemandeur;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getMessagesCount(): ?int
    {
        return $this->messagesCount;
    }

    public function setMessagesCount(int $messagesCount): self
    {
        $this->messagesCount = $messagesCount;
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

    public function getLastMessageAt(): ?\DateTimeInterface
    {
        return $this->lastMessageAt;
    }

    public function setLastMessageAt(?\DateTimeInterface $lastMessageAt): self
    {
        $this->lastMessageAt = $lastMessageAt;
        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;
        return $this;
    }

    public function getClosedByUser(): ?User
    {
        return $this->closedByUser;
    }

    public function setClosedByUser(?User $closedByUser): self
    {
        $this->closedByUser = $closedByUser;
        return $this;
    }

    public function getClosureType(): ?string
    {
        return $this->closureType;
    }

    public function setClosureType(?string $closureType): self
    {
        $this->closureType = $closureType;
        return $this;
    }

    public function getPointsDistributed(): ?bool
    {
        return $this->pointsDistributed;
    }

    public function setPointsDistributed(bool $pointsDistributed): self
    {
        $this->pointsDistributed = $pointsDistributed;
        return $this;
    }

    /**
     * @return Collection|ConversationMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ConversationMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(ConversationMessage $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }
}
