<?php

namespace MozoDev\LetsEncrypt;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MozoDev\LetsEncrypt\TransIP\AccessToken;

class DnsRecordUpdater
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'base_uri' => $_ENV['TRANSIP_API_ENDPOINT'],
            'timeout' => 30
        ]);
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function updateRecord(): bool {
        $accessToken = $this->createToken();
        $response = $this->httpClient->patch("domains/{$_SERVER['CERTBOT_DOMAIN']}/dns", [
            'body' => [
                'name' => '_acme-challenge.',
                'expire' => '60',
                'type' => 'TXT',
                'content' => $_SERVER['CERTBOT_TOKEN']
            ],
            'headers' => [
                'Content-Type' => "application/json",
                'Authorization' => "Bearer {$accessToken}",
            ]
        ]);

        return $response->getStatusCode() === 204;
    }

    /**
     * @throws Exception
     */
    private function createToken(): string {
        $accessToken = new AccessToken();

        return $accessToken->createToken();
    }
}