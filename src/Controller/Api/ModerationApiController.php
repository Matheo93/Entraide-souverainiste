<?php

namespace App\Controller\Api;

use App\Entity\Security\IpBan;
use App\Repository\AnnouncesRepository;
use App\Repository\Security\IpBanRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/moderation')]
class ModerationApiController extends AbstractController
{
    private $em;
    private $announcesRepo;
    private $ipBanRepo;
    private $userRepo;

    public function __construct(
        EntityManagerInterface $em,
        AnnouncesRepository $announcesRepo,
        IpBanRepository $ipBanRepo,
        UserRepository $userRepo
    ) {
        $this->em = $em;
        $this->announcesRepo = $announcesRepo;
        $this->ipBanRepo = $ipBanRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/approve/{id}", name="api_moderation_approve", methods={"POST"})
     */
    public function approve(int $id): JsonResponse
    {
        $announce = $this->announcesRepo->find($id);

        if (!$announce) {
            return new JsonResponse(['error' => 'Annonce introuvable'], 404);
        }

        // Set as active (approved)
        $announce->setIsActive(true);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Annonce #{$id} approuvée",
            'announce_id' => $id
        ]);
    }

    /**
     * @Route("/reject/{id}", name="api_moderation_reject", methods={"POST"})
     */
    public function reject(int $id): JsonResponse
    {
        $announce = $this->announcesRepo->find($id);

        if (!$announce) {
            return new JsonResponse(['error' => 'Annonce introuvable'], 404);
        }

        // Delete the announce (rejected)
        $this->em->remove($announce);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Annonce #{$id} rejetée et supprimée",
            'announce_id' => $id
        ]);
    }

    /**
     * @Route("/ban", name="api_moderation_ban", methods={"POST"})
     */
    public function ban(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $announceId = $data['announceId'] ?? null;
        $ipAddress = $data['ipAddress'] ?? null;
        $reason = $data['reason'] ?? 'Discord moderation ban';

        if (!$ipAddress) {
            return new JsonResponse(['error' => 'IP address required'], 400);
        }

        $announce = null;
        $bannedUser = null;

        if ($announceId) {
            $announce = $this->announcesRepo->find($announceId);
            if ($announce) {
                $bannedUser = $announce->getUser();
                // Delete the announce
                $this->em->remove($announce);
            }
        }

        // Check if IP already banned
        $existingBan = $this->ipBanRepo->findOneBy(['ipAddress' => $ipAddress, 'isActive' => true]);

        if ($existingBan) {
            return new JsonResponse([
                'success' => false,
                'message' => "IP {$ipAddress} est déjà bannie"
            ]);
        }

        // Create IP ban
        $ipBan = new IpBan();
        $ipBan->setIpAddress($ipAddress);
        $ipBan->setReason($reason);
        $ipBan->setBannedAt(new \DateTime());
        $ipBan->setIsActive(true);
        $ipBan->setRelatedAnnounceId($announceId);

        if ($bannedUser) {
            $ipBan->setBannedUser($bannedUser);
        }

        // Admin = system for now (could be improved to track which admin clicked)
        $ipBan->setBannedByAdmin(null);

        $this->em->persist($ipBan);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "IP {$ipAddress} bannie avec succès",
            'ip_address' => $ipAddress,
            'announce_id' => $announceId
        ]);
    }

    /**
     * @Route("/test", name="api_moderation_test", methods={"GET"})
     */
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'message' => 'API moderation is working!',
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }
}
