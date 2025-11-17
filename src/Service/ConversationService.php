<?php

namespace App\Service;

use App\Entity\Announces\Announces;
use App\Entity\Conversations\Conversation;
use App\Entity\Conversations\ConversationMessage;
use App\Entity\User;
use App\Repository\Conversations\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Manages conversations between offrant and demandeur
 * Handles automatic status transitions based on message count
 */
class ConversationService
{
    private $em;
    private $conversationRepo;
    private $pointsService;
    private $statsService;

    public function __construct(
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo,
        PointsDistributionService $pointsService,
        BehaviorStatsService $statsService
    ) {
        $this->em = $em;
        $this->conversationRepo = $conversationRepo;
        $this->pointsService = $pointsService;
        $this->statsService = $statsService;
    }

    /**
     * Start a new conversation or return existing active one
     */
    public function startConversation(
        Announces $announce,
        User $demandeur,
        string $initialMessage
    ): Conversation {
        // Check if active conversation already exists
        $existing = $this->conversationRepo->findActiveConversation(
            $announce,
            $announce->getUser(), // offrant
            $demandeur
        );

        if ($existing) {
            // Add message to existing conversation
            $this->addMessage($existing, $demandeur, $initialMessage);
            return $existing;
        }

        // Create new conversation
        $conversation = new Conversation();
        $conversation->setAnnounce($announce);
        $conversation->setUserOffrant($announce->getUser());
        $conversation->setUserDemandeur($demandeur);
        $conversation->setStatus('EN_ATTENTE_REPONSE');

        $this->em->persist($conversation);
        $this->addMessage($conversation, $demandeur, $initialMessage);
        $this->em->flush();

        return $conversation;
    }

    /**
     * Add a message to conversation
     * Updates message count and status automatically
     */
    public function addMessage(
        Conversation $conversation,
        User $sender,
        string $messageContent
    ): ConversationMessage {
        $message = new ConversationMessage();
        $message->setConversation($conversation);
        $message->setSenderUser($sender);
        $message->setMessage($messageContent);
        $message->setSentAt(new \DateTime());

        $this->em->persist($message);

        // Update conversation
        $conversation->setMessagesCount($conversation->getMessagesCount() + 1);
        $conversation->setLastMessageAt(new \DateTime());

        // Auto-update status based on message count
        $count = $conversation->getMessagesCount();
        if ($count === 2) {
            $conversation->setStatus('EN_COURS');
        } elseif ($count >= 3) {
            $conversation->setStatus('ACTIF');
            // 🎯 At 3 messages, UI will show close buttons
        }

        $this->em->flush();
        return $message;
    }

    /**
     * Close conversation with specified closure type
     * Distributes invisible points if agreement reached
     */
    public function closeConversation(
        Conversation $conversation,
        User $closingUser,
        string $closureType // 'ACCORD', 'DESACCORD', 'EN_COURS'
    ): void {
        $conversation->setClosedAt(new \DateTime());
        $conversation->setClosedByUser($closingUser);
        $conversation->setClosureType($closureType);

        if ($closureType === 'ACCORD') {
            $conversation->setStatus('CLOTURE_ACCORD');

            // Distribute invisible points
            if (!$conversation->getPointsDistributed()) {
                $this->pointsService->distributePoints(
                    $conversation->getUserOffrant(),  // +5 points
                    $conversation->getUserDemandeur(), // -3 points
                    $conversation
                );
                $conversation->setPointsDistributed(true);
            }
        } elseif ($closureType === 'DESACCORD') {
            $conversation->setStatus('CLOTURE_DESACCORD');
            // No points distributed
        } elseif ($closureType === 'EN_COURS') {
            // Conversation remains open
            return;
        }

        $this->em->flush();

        // Update invisible behavior stats for both users
        $this->statsService->updateUserStats($conversation->getUserOffrant());
        $this->statsService->updateUserStats($conversation->getUserDemandeur());
    }

    /**
     * Auto-close conversations older than 7 days
     * Called by CRON job
     */
    public function autoCloseOldConversations(): int
    {
        $conversations = $this->conversationRepo->findOldConversations(7);
        $closedCount = 0;

        foreach ($conversations as $conversation) {
            // ≥5 messages = considered successful (implicit agreement)
            // <5 messages = no agreement
            $closureType = $conversation->getMessagesCount() >= 5 ? 'ACCORD' : 'DESACCORD';

            $conversation->setStatus('CLOTURE_AUTO');
            $conversation->setClosureType($closureType);
            $conversation->setClosedAt(new \DateTime());

            if ($closureType === 'ACCORD' && !$conversation->getPointsDistributed()) {
                $this->pointsService->distributePoints(
                    $conversation->getUserOffrant(),
                    $conversation->getUserDemandeur(),
                    $conversation
                );
                $conversation->setPointsDistributed(true);
            }

            $this->statsService->updateUserStats($conversation->getUserOffrant());
            $this->statsService->updateUserStats($conversation->getUserDemandeur());

            $closedCount++;
        }

        $this->em->flush();
        return $closedCount;
    }

    /**
     * Get all conversations for a user
     */
    public function getUserConversations(User $user): array
    {
        return $this->conversationRepo->findUserConversations($user);
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(Conversation $conversation, User $user): void
    {
        foreach ($conversation->getMessages() as $message) {
            if ($message->getSenderUser() !== $user && !$message->getIsRead()) {
                $message->setIsRead(true);
            }
        }
        $this->em->flush();
    }
}
