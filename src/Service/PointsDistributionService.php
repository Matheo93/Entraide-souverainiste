<?php

namespace App\Service;

use App\Entity\Behavior\PointsTransaction;
use App\Entity\Behavior\UserBehaviorStats;
use App\Entity\Conversations\Conversation;
use App\Entity\User;
use App\Repository\Behavior\UserBehaviorStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * INVISIBLE POINTS SYSTEM
 * Users NEVER see their points balance
 * This is purely for detecting profiteurs via P2P ratio
 */
class PointsDistributionService
{
    private const POINTS_OFFER_SUCCESS = 5;   // Offrant earns when agreement
    private const POINTS_DEMAND_COST = -3;     // Demandeur spends per demand
    private const STARTING_BALANCE = 10;

    private $em;
    private $statsRepo;

    public function __construct(
        EntityManagerInterface $em,
        UserBehaviorStatsRepository $statsRepo
    ) {
        $this->em = $em;
        $this->statsRepo = $statsRepo;
    }

    /**
     * Distribute points after conversation closes with agreement
     * Offrant: +5 points to pointsOffres (provided service)
     * Demandeur: +3 points to pointsDemandes (received service)
     */
    public function distributePoints(
        User $userOffrant,
        User $userDemandeur,
        Conversation $conversation
    ): void {
        // Get or create behavior stats for both users
        $statsOffrant = $this->getOrCreateStats($userOffrant);
        $statsDemandeur = $this->getOrCreateStats($userDemandeur);

        // Offrant earns points (adds to pointsOffres)
        $newOffresBalance = $statsOffrant->getPointsOffres() + self::POINTS_OFFER_SUCCESS;
        $statsOffrant->setPointsOffres($newOffresBalance);

        // Record transaction
        $this->recordTransaction(
            $userOffrant,
            self::POINTS_OFFER_SUCCESS,
            $newOffresBalance,
            'OFFER_COMPLETED',
            $conversation,
            sprintf(
                'Service fourni: %s (conversation #%d)',
                $conversation->getAnnounce()->getTitle(),
                $conversation->getId()
            )
        );

        // Demandeur earns points (adds to pointsDemandes - all positive now)
        $pointsDemandePositive = abs(self::POINTS_DEMAND_COST); // Convert to positive: 3
        $newDemandesBalance = $statsDemandeur->getPointsDemandes() + $pointsDemandePositive;
        $statsDemandeur->setPointsDemandes($newDemandesBalance);

        // Record transaction
        $this->recordTransaction(
            $userDemandeur,
            $pointsDemandePositive,
            $newDemandesBalance,
            'DEMAND_FULFILLED',
            $conversation,
            sprintf(
                'Service reçu: %s (conversation #%d)',
                $conversation->getAnnounce()->getTitle(),
                $conversation->getId()
            )
        );

        $this->em->flush();
    }

    /**
     * Record a points transaction for audit trail (admin visibility)
     */
    private function recordTransaction(
        User $user,
        int $pointsChange,
        int $balanceAfter,
        string $type,
        ?Conversation $conversation,
        ?string $details
    ): void {
        $transaction = new PointsTransaction();
        $transaction->setUser($user);
        $transaction->setPointsChange($pointsChange);
        $transaction->setBalanceAfter($balanceAfter);
        $transaction->setTransactionType($type);
        $transaction->setRelatedConversation($conversation);
        $transaction->setDetails($details);

        $this->em->persist($transaction);
    }

    /**
     * Get or create behavior stats for user
     * Users start with 0 points (all positive system)
     */
    private function getOrCreateStats(User $user): UserBehaviorStats
    {
        $stats = $this->statsRepo->findOneBy(['user' => $user]);

        if (!$stats) {
            $stats = new UserBehaviorStats();
            $stats->setUser($user);
            $stats->setPointsOffres(0);
            $stats->setPointsDemandes(0);
            $this->em->persist($stats);
            $this->em->flush();
        }

        return $stats;
    }

    /**
     * Check user's P2P ratio (for manual admin review only)
     * NOT used for automatic limitations
     */
    public function getUserRatio(User $user): float
    {
        $stats = $this->getOrCreateStats($user);
        return $stats->getPointsRatio();
    }

    /**
     * Get user's invisible points stats (admin only)
     */
    public function getUserPointsStats(User $user): array
    {
        $stats = $this->getOrCreateStats($user);
        return [
            'pointsOffres' => $stats->getPointsOffres(),
            'pointsDemandes' => $stats->getPointsDemandes(),
            'ratio' => $stats->getPointsRatio()
        ];
    }

    /**
     * Admin: manually adjust points (for exceptional cases)
     * $pointsType: 'offres' or 'demandes'
     */
    public function adjustPoints(
        User $user,
        int $pointsChange,
        string $pointsType,
        string $reason
    ): void {
        $stats = $this->getOrCreateStats($user);

        if ($pointsType === 'offres') {
            $newBalance = $stats->getPointsOffres() + $pointsChange;
            $stats->setPointsOffres(max(0, $newBalance)); // Never negative
        } else {
            $newBalance = $stats->getPointsDemandes() + $pointsChange;
            $stats->setPointsDemandes(max(0, $newBalance)); // Never negative
        }

        $this->recordTransaction(
            $user,
            $pointsChange,
            $newBalance,
            'ADMIN_ADJUSTMENT',
            null,
            sprintf('%s (%s)', $reason, $pointsType)
        );

        $this->em->flush();
    }
}
