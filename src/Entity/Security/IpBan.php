<?php

namespace App\Entity\Security;

use App\Entity\User;
use App\Repository\Security\IpBanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * IP BAN SYSTEM
 * Tracks banned IP addresses from Discord moderation
 * Prevents announcement creation from banned IPs
 *
 * @ORM\Entity(repositoryClass=IpBanRepository::class)
 * @ORM\Table(name="ip_bans")
 */
class IpBan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, unique=true)
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $bannedUser;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $bannedByAdmin;

    /**
     * @ORM\Column(type="datetime")
     */
    private $bannedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive = true;

    /**
     * Related announcement ID that triggered the ban
     * @ORM\Column(type="integer", nullable=true)
     */
    private $relatedAnnounceId;

    public function __construct()
    {
        $this->bannedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        return $this;
    }

    public function getBannedUser(): ?User
    {
        return $this->bannedUser;
    }

    public function setBannedUser(?User $bannedUser): self
    {
        $this->bannedUser = $bannedUser;
        return $this;
    }

    public function getBannedByAdmin(): ?User
    {
        return $this->bannedByAdmin;
    }

    public function setBannedByAdmin(?User $bannedByAdmin): self
    {
        $this->bannedByAdmin = $bannedByAdmin;
        return $this;
    }

    public function getBannedAt(): ?\DateTimeInterface
    {
        return $this->bannedAt;
    }

    public function setBannedAt(\DateTimeInterface $bannedAt): self
    {
        $this->bannedAt = $bannedAt;
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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getRelatedAnnounceId(): ?int
    {
        return $this->relatedAnnounceId;
    }

    public function setRelatedAnnounceId(?int $relatedAnnounceId): self
    {
        $this->relatedAnnounceId = $relatedAnnounceId;
        return $this;
    }

    /**
     * Check if ban is still active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->expiresAt && $this->expiresAt < new \DateTime()) {
            return false;
        }

        return true;
    }
}
