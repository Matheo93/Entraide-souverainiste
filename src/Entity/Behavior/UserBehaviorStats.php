<?php

namespace App\Entity\Behavior;

use App\Entity\User;
use App\Repository\Behavior\UserBehaviorStatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * INVISIBLE SYSTEM - Users NEVER see this data
 * Used to detect profiteurs (P2P ratio enforcement)
 *
 * @ORM\Entity(repositoryClass=UserBehaviorStatsRepository::class)
 * @ORM\Table(name="user_behavior_stats")
 */
class UserBehaviorStats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, unique=true)
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalOffres = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalDemandes = 0;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $ratioOffresDemandes = 1.00;

    /**
     * @ORM\Column(type="integer")
     */
    private $discussionsTotal = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $discussionsAvecAccord = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $discussionsAbandonnees = 0;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $tauxAbandon = 0.00;

    /**
     * @ORM\Column(type="integer")
     */
    private $messagesTotal = 0;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $messagesMoyenParDiscussion = 0.00;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $tempsReponseMoyenHeures = 0.00;

    /**
     * PROFITEUR DETECTION SCORE (0-100)
     * 0-30: Normal user
     * 30-60: Suspect (light moderation)
     * 60-100: PROFITEUR (heavy limitations)
     *
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $profiteurScore = 0.00;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $profiteurLevel = 'NORMAL';

    /**
     * POSITIVE POINTS SYSTEM (admin can see via SQL)
     * Each OFFRE created: +5 points
     * Each DEMANDE created: +3 points
     * Ratio = pointsOffres / pointsDemandes (target >= 1.0)
     *
     * @ORM\Column(type="integer")
     */
    private $pointsOffres = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $pointsDemandes = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastCalculatedAt;

    public function __construct()
    {
        $this->lastCalculatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTotalOffres(): ?int
    {
        return $this->totalOffres;
    }

    public function setTotalOffres(int $totalOffres): self
    {
        $this->totalOffres = $totalOffres;
        return $this;
    }

    public function getTotalDemandes(): ?int
    {
        return $this->totalDemandes;
    }

    public function setTotalDemandes(int $totalDemandes): self
    {
        $this->totalDemandes = $totalDemandes;
        return $this;
    }

    public function getRatioOffresDemandes(): ?string
    {
        return $this->ratioOffresDemandes;
    }

    public function setRatioOffresDemandes(string $ratioOffresDemandes): self
    {
        $this->ratioOffresDemandes = $ratioOffresDemandes;
        return $this;
    }

    public function getDiscussionsTotal(): ?int
    {
        return $this->discussionsTotal;
    }

    public function setDiscussionsTotal(int $discussionsTotal): self
    {
        $this->discussionsTotal = $discussionsTotal;
        return $this;
    }

    public function getDiscussionsAvecAccord(): ?int
    {
        return $this->discussionsAvecAccord;
    }

    public function setDiscussionsAvecAccord(int $discussionsAvecAccord): self
    {
        $this->discussionsAvecAccord = $discussionsAvecAccord;
        return $this;
    }

    public function getDiscussionsAbandonnees(): ?int
    {
        return $this->discussionsAbandonnees;
    }

    public function setDiscussionsAbandonnees(int $discussionsAbandonnees): self
    {
        $this->discussionsAbandonnees = $discussionsAbandonnees;
        return $this;
    }

    public function getTauxAbandon(): ?string
    {
        return $this->tauxAbandon;
    }

    public function setTauxAbandon(string $tauxAbandon): self
    {
        $this->tauxAbandon = $tauxAbandon;
        return $this;
    }

    public function getMessagesTotal(): ?int
    {
        return $this->messagesTotal;
    }

    public function setMessagesTotal(int $messagesTotal): self
    {
        $this->messagesTotal = $messagesTotal;
        return $this;
    }

    public function getMessagesMoyenParDiscussion(): ?string
    {
        return $this->messagesMoyenParDiscussion;
    }

    public function setMessagesMoyenParDiscussion(string $messagesMoyenParDiscussion): self
    {
        $this->messagesMoyenParDiscussion = $messagesMoyenParDiscussion;
        return $this;
    }

    public function getTempsReponseMoyenHeures(): ?string
    {
        return $this->tempsReponseMoyenHeures;
    }

    public function setTempsReponseMoyenHeures(string $tempsReponseMoyenHeures): self
    {
        $this->tempsReponseMoyenHeures = $tempsReponseMoyenHeures;
        return $this;
    }

    public function getProfiteurScore(): ?string
    {
        return $this->profiteurScore;
    }

    public function setProfiteurScore(string $profiteurScore): self
    {
        $this->profiteurScore = $profiteurScore;
        return $this;
    }

    public function getProfiteurLevel(): ?string
    {
        return $this->profiteurLevel;
    }

    public function setProfiteurLevel(string $profiteurLevel): self
    {
        $this->profiteurLevel = $profiteurLevel;
        return $this;
    }

    public function getPointsOffres(): ?int
    {
        return $this->pointsOffres;
    }

    public function setPointsOffres(int $pointsOffres): self
    {
        $this->pointsOffres = $pointsOffres;
        return $this;
    }

    public function getPointsDemandes(): ?int
    {
        return $this->pointsDemandes;
    }

    public function setPointsDemandes(int $pointsDemandes): self
    {
        $this->pointsDemandes = $pointsDemandes;
        return $this;
    }

    /**
     * Calculate points ratio (offres/demandes)
     * Target: >= 1.0 (balanced or generous)
     */
    public function getPointsRatio(): float
    {
        if ($this->pointsDemandes == 0) {
            return 999.99; // Infinite ratio (only offers, no demands)
        }
        return round($this->pointsOffres / $this->pointsDemandes, 2);
    }

    public function getLastCalculatedAt(): ?\DateTimeInterface
    {
        return $this->lastCalculatedAt;
    }

    public function setLastCalculatedAt(?\DateTimeInterface $lastCalculatedAt): self
    {
        $this->lastCalculatedAt = $lastCalculatedAt;
        return $this;
    }
}
