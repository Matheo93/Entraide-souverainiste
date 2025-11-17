<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    private $httpClient;
    private $secretKey;

    public function __construct(HttpClientInterface $httpClient, string $recaptchaSecretKey)
    {
        $this->httpClient = $httpClient;
        $this->secretKey = $recaptchaSecretKey;
    }

    public function verify(string $recaptchaResponse, string $remoteIp = null): bool
    {
        if (empty($recaptchaResponse)) {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $recaptchaResponse,
                    'remoteip' => $remoteIp
                ]
            ]);

            $data = $response->toArray();

            return isset($data['success']) && $data['success'] === true;
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas l'utilisateur
            error_log('reCAPTCHA verification failed: ' . $e->getMessage());
            return false;
        }
    }
}
