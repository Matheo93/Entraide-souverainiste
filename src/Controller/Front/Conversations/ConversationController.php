<?php

namespace App\Controller\Front\Conversations;

use App\Entity\Conversations\Conversation;
use App\Entity\Conversations\ConversationMessage;
use App\Repository\Conversations\ConversationRepository;
use App\Repository\AnnouncesRepository;
use App\Service\ConversationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/conversations')]
class ConversationController extends AbstractController
{
    private $conversationRepo;
    private $announcesRepo;
    private $conversationService;
    private $em;

    public function __construct(
        ConversationRepository $conversationRepo,
        AnnouncesRepository $announcesRepo,
        ConversationService $conversationService,
        EntityManagerInterface $em
    ) {
        $this->conversationRepo = $conversationRepo;
        $this->announcesRepo = $announcesRepo;
        $this->conversationService = $conversationService;
        $this->em = $em;
    }

    /**
     * @Route("/list", name="conversations_list_api", methods={"GET"})
     */
    public function listApi(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $conversations = $this->conversationRepo->findUserConversations($user);

        $data = [];
        foreach ($conversations as $conv) {
            $otherUser = $conv->getUserOffrant() === $user
                ? $conv->getUserDemandeur()
                : $conv->getUserOffrant();

            $data[] = [
                'id' => $conv->getId(),
                'announce_title' => $conv->getAnnounce()->getTitle(),
                'other_user' => $otherUser->getEmail(),
                'status' => $conv->getStatus(),
                'messages_count' => $conv->getMessagesCount(),
                'last_message_at' => $conv->getLastMessageAt()
                    ? $conv->getLastMessageAt()->format('Y-m-d H:i:s')
                    : null,
                'unread_count' => $this->getUnreadCount($conv, $user)
            ];
        }

        return new JsonResponse(['conversations' => $data]);
    }

    private function getUnreadCount(Conversation $conversation, $user): int
    {
        $count = 0;
        foreach ($conversation->getMessages() as $message) {
            if ($message->getSenderUser() !== $user && !$message->getIsRead()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @Route("/start/{announceId}", name="conversation_start", methods={"POST"})
     */
    public function start(int $announceId): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $announce = $this->announcesRepo->find($announceId);

        if (!$announce) {
            $this->addFlash('error', 'Annonce introuvable');
            return $this->redirectToRoute('home');
        }

        // Check if conversation already exists
        $existingConv = $this->conversationRepo->findOneBy([
            'announce' => $announce,
            'userDemandeur' => $user
        ]);

        if ($existingConv) {
            return $this->redirectToRoute('conversation_view', ['id' => $existingConv->getId()]);
        }

        // Create new conversation
        $conversation = $this->conversationService->startConversation($announce, $user);

        $this->addFlash('success', 'Conversation démarrée');
        return $this->redirectToRoute('conversation_view', ['id' => $conversation->getId()]);
    }

    /**
     * @Route("/{id}/view", name="conversation_view_api", methods={"GET"})
     */
    public function viewApi(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $conversation = $this->conversationRepo->find($id);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        // Check user is part of conversation
        if ($conversation->getUserOffrant() !== $user && $conversation->getUserDemandeur() !== $user) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        // Mark messages as read
        foreach ($conversation->getMessages() as $message) {
            if ($message->getSenderUser() !== $user && !$message->getIsRead()) {
                $message->setIsRead(true);
            }
        }
        $this->em->flush();

        $otherUser = $conversation->getUserOffrant() === $user
            ? $conversation->getUserDemandeur()
            : $conversation->getUserOffrant();

        return new JsonResponse([
            'conversation' => [
                'id' => $conversation->getId(),
                'announce_title' => $conversation->getAnnounce()->getTitle(),
                'other_user' => $otherUser->getEmail(),
                'status' => $conversation->getStatus(),
                'can_close' => !in_array($conversation->getStatus(), ['CLOTURE_ACCORD', 'CLOTURE_DESACCORD'])
            ]
        ]);
    }

    /**
     * @Route("/{id}/send", name="conversation_send_message", methods={"POST"})
     */
    public function sendMessage(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $conversation = $this->conversationRepo->find($id);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        // Check user is part of conversation
        if ($conversation->getUserOffrant() !== $user && $conversation->getUserDemandeur() !== $user) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        // Check if conversation is closed
        if (in_array($conversation->getStatus(), ['CLOTURE_ACCORD', 'CLOTURE_DESACCORD'])) {
            return new JsonResponse(['error' => 'Conversation fermée'], 400);
        }

        $messageText = $request->request->get('message');

        if (empty(trim($messageText))) {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        // Add message
        $message = $this->conversationService->addMessage($conversation, $user, $messageText);

        return new JsonResponse([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'text' => $message->getMessage(),
                'sentAt' => $message->getSentAt()->format('d/m/Y H:i'),
                'isOwn' => true
            ]
        ]);
    }

    /**
     * @Route("/{id}/close/{type}", name="conversation_close", methods={"POST"})
     */
    public function close(int $id, string $type): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $conversation = $this->conversationRepo->find($id);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        // Check user is part of conversation
        if ($conversation->getUserOffrant() !== $user && $conversation->getUserDemandeur() !== $user) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        if (!in_array($type, ['ACCORD', 'DESACCORD'])) {
            return new JsonResponse(['error' => 'Type de clôture invalide'], 400);
        }

        $this->conversationService->closeConversation($conversation, $user, $type);

        $message = $type === 'ACCORD'
            ? 'Conversation fermée avec accord. Les points ont été distribués.'
            : 'Conversation fermée sans accord.';

        return new JsonResponse(['success' => true, 'message' => $message]);
    }

    /**
     * @Route("/{id}/messages", name="conversation_get_messages", methods={"GET"})
     */
    public function getMessages(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $conversation = $this->conversationRepo->find($id);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        // Check user is part of conversation
        if ($conversation->getUserOffrant() !== $user && $conversation->getUserDemandeur() !== $user) {
            return new JsonResponse(['error' => 'Accès non autorisé'], 403);
        }

        $messages = [];
        foreach ($conversation->getMessages() as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'text' => $message->getMessage(),
                'sentAt' => $message->getSentAt()->format('d/m/Y H:i'),
                'isOwn' => $message->getSenderUser() === $user,
                'senderEmail' => $message->getSenderUser()->getEmail()
            ];
        }

        return new JsonResponse(['messages' => $messages]);
    }
}
