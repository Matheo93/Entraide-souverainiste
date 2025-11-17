<?php

namespace App\Service;

use App\Entity\Behavior\UserBehaviorStats;
use App\Entity\Behavior\UserLimitation;
use App\Entity\User;
use App\Repository\AnnouncesRepository;
use App\Repository\Behavior\UserBehaviorStatsRepository;
use App\Repository\Conversations\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * INVISIBLE PROFITEUR DETECTION SYSTEM
 * Calculates behavior score (0-100) based on:
 * - Ratio offres/demandes
 * - Abandon rate
 * - Engagement level
 * - Response time
 *
 * Automatically applies silent limitations
 */
class BehaviorStatsService
{
    private $em;
    private $statsRepo;
    private $conversationRepo;
    private $announcesRepo;

    public function __construct(
        EntityManagerInterface $em,
        UserBehaviorStatsRepository $statsRepo,
        ConversationRepository $conversationRepo,
        AnnouncesRepository $announcesRepo
    ) {
        $this->em = $em;
        $this->statsRepo = $statsRepo;
        $this->conversationRepo = $conversationRepo;
        $this->announcesRepo = $announcesRepo;
    }

    /**
     * Update all behavior statistics for a user
     * Called after each conversation close
     */
    public function updateUserStats(User $user): void
    {
        $stats = $this->getOrCreateStats($user);

        // Count offres and demandes
        $totalOffres = $this->announcesRepo->count([
            'user' => $user,
            'type' => 'offre'
        ]);

        $totalDemandes = $this->announcesRepo->count([
            'user' => $user,
            'type' => 'demande'
        ]);

        $stats->setTotalOffres($totalOffres);
        $stats->setTotalDemandes($totalDemandes);

        // Calculate ratio (avoid division by zero)
        $ratio = $totalDemandes > 0
            ? round($totalOffres / $totalDemandes, 2)
            : 1.00;
        $stats->setRatioOffresDemandes($ratio);

        // Conversation statistics
        $conversations = $this->conversationRepo->findUserConversations($user);
        $discussionsTotal = count($conversations);
        $discussionsAvecAccord = 0;
        $discussionsAbandonnees = 0;
        $messagesTotal = 0;

        foreach ($conversations as $conv) {
            $messagesTotal += $conv->getMessagesCount();

            if ($conv->getStatus() === 'CLOTURE_ACCORD') {
                $discussionsAvecAccord++;
            } elseif ($conv->getStatus() === 'CLOTURE_DESACCORD') {
                $discussionsAbandonnees++;
            }
        }

        $stats->setDiscussionsTotal($discussionsTotal);
        $stats->setDiscussionsAvecAccord($discussionsAvecAccord);
        $stats->setDiscussionsAbandonnees($discussionsAbandonnees);

        // Taux abandon
        $tauxAbandon = $discussionsTotal > 0
            ? round($discussionsAbandonnees / $discussionsTotal, 2)
            : 0.00;
        $stats->setTauxAbandon($tauxAbandon);

        // Messages stats
        $stats->setMessagesTotal($messagesTotal);
        $messagesMoyen = $discussionsTotal > 0
            ? round($messagesTotal / $discussionsTotal, 2)
            : 0.00;
        $stats->setMessagesMoyenParDiscussion($messagesMoyen);

        // TODO: Calculate average response time (requires message timestamps analysis)
        // For now, set default
        $stats->setTempsReponseMoyenHeures(0.00);

        // Calculate profiteur score
        $profiteurScore = $this->calculateProfiteurScore($stats);
        $stats->setProfiteurScore($profiteurScore);

        // Determine level
        $level = 'NORMAL';
        if ($profiteurScore >= 60) {
            $level = 'PROFITEUR';
        } elseif ($profiteurScore >= 40) {
            $level = 'SUSPECT';
        }
        $stats->setProfiteurLevel($level);

        $stats->setLastCalculatedAt(new \DateTime());

        $this->em->flush();

        // NO automatic moderation - admin decides manually
    }

    /**
     * PROFITEUR SCORE ALGORITHM (0-100)
     *
     * Scoring:
     * 1. Ratio offres/demandes:
     *    - <0.3 (many demands, few offers): +40 points
     *    - <0.5: +20 points
     *
     * 2. Abandon rate:
     *    - >50% abandoned: +30 points
     *    - >30% abandoned: +15 points
     *
     * 3. Engagement (messages per discussion):
     *    - <3 messages average: +20 points
     *
     * 4. Response time:
     *    - >48h average: +10 points
     *
     * Levels:
     * - 0-30: NORMAL
     * - 30-60: SUSPECT
     * - 60-100: PROFITEUR
     */
    private function calculateProfiteurScore(UserBehaviorStats $stats): float
    {
        $score = 0;

        // 1. Ratio check
        $ratio = (float) $stats->getRatioOffresDemandes();
        if ($ratio < 0.3) {
            $score += 40; // Heavily unbalanced (many demands, few offers)
        } elseif ($ratio < 0.5) {
            $score += 20;
        }

        // 2. Abandon rate
        $tauxAbandon = (float) $stats->getTauxAbandon();
        if ($tauxAbandon > 0.5) {
            $score += 30; // Abandons most conversations
        } elseif ($tauxAbandon > 0.3) {
            $score += 15;
        }

        // 3. Engagement (messages)
        $messagesMoyen = (float) $stats->getMessagesMoyenParDiscussion();
        if ($messagesMoyen < 3 && $stats->getDiscussionsTotal() > 0) {
            $score += 20; // Very low engagement
        }

        // 4. Response time
        $tempsReponse = (float) $stats->getTempsReponseMoyenHeures();
        if ($tempsReponse > 48) {
            $score += 10; // Very slow to respond
        }

        return min($score, 100);
    }

    /**
     * Get or create stats for user
     */
    private function getOrCreateStats(User $user): UserBehaviorStats
    {
        $stats = $this->statsRepo->findOneBy(['user' => $user]);

        if (!$stats) {
            $stats = new UserBehaviorStats();
            $stats->setUser($user);
            // Points: +5 per offre, +3 per demande (all positive)
            $stats->setPointsOffres(0);
            $stats->setPointsDemandes(0);
            $this->em->persist($stats);
            $this->em->flush();
        }

        return $stats;
    }

    /**
     * Admin: Get profiteurs list
     */
    public function getProfiteurs(): array
    {
        return $this->statsRepo->findProfiteurs();
    }

    /**
     * Admin: Get suspects list
     */
    public function getSuspects(): array
    {
        return $this->statsRepo->findSuspects();
    }
}
