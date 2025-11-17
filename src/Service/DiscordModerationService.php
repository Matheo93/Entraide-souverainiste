<?php

namespace App\Service;

use App\Entity\Announces\Announces;
use App\Entity\User;
use App\Repository\Security\IpBanRepository;
use App\Repository\Behavior\UserBehaviorStatsRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Discord webhook integration for announcement moderation
 * Sends webhooks to Discord moderation channel
 * Handles admin approval/rejection/ban via Discord buttons
 */
class DiscordModerationService
{
    private $httpClient;
    private $webhookUrl;
    private $ipBanRepository;
    private $statsRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        IpBanRepository $ipBanRepository,
        UserBehaviorStatsRepository $statsRepository,
        string $discordModerationWebhookUrl
    ) {
        $this->httpClient = $httpClient;
        $this->ipBanRepository = $ipBanRepository;
        $this->statsRepository = $statsRepository;
        $this->webhookUrl = $discordModerationWebhookUrl;
    }

    /**
     * Send announcement to Discord for moderation
     * Announcement status: PENDING → waits for admin action
     */
    public function sendForModeration(Announces $announce): void
    {
        if (empty($this->webhookUrl)) {
            // Discord integration not configured
            return;
        }

        $user = $announce->getUser();
        // Load stats separately to keep them invisible to user
        $userStats = $this->statsRepository->findOneBy(['user' => $user]);

        // Get announce type from metas
        $announceMetas = $announce->getAnnouncesMetas();
        $announceType = 'demande'; // default
        if ($announceMetas) {
            $metas = $announceMetas->getMetas();
            $announceType = $metas['isOfferOrProposal'] ?? 'demande';
        }

        // Build Discord embed
        $embed = [
            'title' => '📢 Nouvelle annonce à modérer',
            'description' => $announce->getContent(), // Using 'content' field
            'color' => $announceType === 'offre' ? 0x28a745 : 0x007bff,
            'fields' => [
                [
                    'name' => 'Titre',
                    'value' => $announce->getTitle(),
                    'inline' => false
                ],
                [
                    'name' => 'Type',
                    'value' => strtoupper($announceType),
                    'inline' => true
                ],
                [
                    'name' => 'Catégorie',
                    'value' => $announce->getCategories()->first() ? $announce->getCategories()->first()->getName() : 'N/A',
                    'inline' => true
                ],
                [
                    'name' => 'Utilisateur',
                    'value' => sprintf('%s (%s)', $user->getFullName(), $user->getEmail()),
                    'inline' => false
                ],
                [
                    'name' => '🎯 Score Profiteur',
                    'value' => $userStats
                        ? sprintf('%.2f/100 (%s)', $userStats->getProfiteurScore(), $userStats->getProfiteurLevel())
                        : 'N/A',
                    'inline' => true
                ],
                [
                    'name' => '⚖️ Ratio Offres/Demandes',
                    'value' => $userStats
                        ? sprintf('%.2f', $userStats->getRatioOffresDemandes())
                        : 'N/A',
                    'inline' => true
                ],
                [
                    'name' => '💰 Points Ratio',
                    'value' => $userStats
                        ? sprintf('Offres:%d Demandes:%d (%.2f)',
                            $userStats->getPointsOffres(),
                            $userStats->getPointsDemandes(),
                            $userStats->getPointsRatio())
                        : 'N/A',
                    'inline' => true
                ]
            ],
            'footer' => [
                'text' => sprintf('Annonce ID: %d | IP: %s', $announce->getId(), $this->getUserIP())
            ],
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
        ];

        // Add URL to announcement if available
        if ($announce->getId()) {
            $embed['url'] = sprintf(
                'https://action-sociale.cerclearistote.com/annonce/%d',
                $announce->getId()
            );
        }

        $payload = [
            'embeds' => [$embed],
            'components' => [
                [
                    'type' => 1, // Action Row
                    'components' => [
                        [
                            'type' => 2, // Button
                            'style' => 3, // Success (green)
                            'label' => '✅ Approuver',
                            'custom_id' => sprintf('approve_%d', $announce->getId())
                        ],
                        [
                            'type' => 2,
                            'style' => 4, // Danger (red)
                            'label' => '❌ Rejeter',
                            'custom_id' => sprintf('reject_%d', $announce->getId())
                        ],
                        [
                            'type' => 2,
                            'style' => 4,
                            'label' => '🚫 Ban IP',
                            'custom_id' => sprintf('ban_%d_%s', $announce->getId(), $this->getUserIP())
                        ]
                    ]
                ]
            ]
        ];

        try {
            $this->httpClient->request('POST', $this->webhookUrl, [
                'json' => $payload
            ]);
        } catch (\Exception $e) {
            // Log error but don't block announcement creation
            error_log('Discord webhook failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notification of admin action back to Discord
     */
    public function notifyAction(string $action, Announces $announce, string $adminName): void
    {
        if (empty($this->webhookUrl)) {
            return;
        }

        $colors = [
            'approved' => 0x28a745, // Green
            'rejected' => 0xdc3545, // Red
            'banned' => 0x000000    // Black
        ];

        $embed = [
            'title' => sprintf('🛡️ Annonce %s', strtoupper($action)),
            'description' => $announce->getTitle(),
            'color' => $colors[$action] ?? 0x6c757d,
            'fields' => [
                [
                    'name' => 'Action par',
                    'value' => $adminName,
                    'inline' => true
                ],
                [
                    'name' => 'Annonce ID',
                    'value' => (string) $announce->getId(),
                    'inline' => true
                ]
            ],
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM)
        ];

        try {
            $this->httpClient->request('POST', $this->webhookUrl, [
                'json' => ['embeds' => [$embed]]
            ]);
        } catch (\Exception $e) {
            error_log('Discord notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user IP address (for ban tracking)
     */
    private function getUserIP(): string
    {
        // Check for proxy headers
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // If multiple IPs (proxy chain), take first
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }

        return $ip;
    }

    /**
     * Check if IP is banned
     */
    public function isIPBanned(string $ip): bool
    {
        return $this->ipBanRepository->isIpBanned($ip);
    }

    /**
     * Get current user IP for tracking
     */
    public function getCurrentUserIP(): string
    {
        return $this->getUserIP();
    }
}
