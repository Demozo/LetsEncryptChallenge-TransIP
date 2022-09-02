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
    public function updateRecord(): bool
    {
        Program::$logger->info("Updating DNS record to [{$_SERVER['CERTBOT_VALIDATION']}]");

        $accessToken = $this->createToken();
        $response = $this->httpClient->patch("domains/{$_SERVER['CERTBOT_DOMAIN']}/dns", [
            'body' => json_encode([
                'dnsEntry' => [
                    'name' => '_acme-challenge',
                    'expire' => '60',
                    'type' => 'TXT',
                    'content' => $_SERVER['CERTBOT_VALIDATION']
                ],
            ]),
            'headers' => [
                'Content-Type' => "application/json",
                'Authorization' => "Bearer {$accessToken}",
            ]
        ]);

        set_time_limit(180);
        Program::$logger->info('Waiting for 2 minutes');
        sleep(60);
        Program::$logger->info('1 minute left');
        sleep(60);

        return $response->getStatusCode() === 204;
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function cleanup(): bool {
        $accessToken = $this->createToken();
        $response = $this->httpClient->delete("domains/{$_SERVER['CERTBOT_DOMAIN']}/dns", [
            'body' => json_encode([
                'dnsEntry' => [
                    'name' => '_acme-challenge',
                    'expire' => '60',
                    'type' => 'TXT',
                    'content' => $_SERVER['CERTBOT_AUTH_OUTPUT']
                ],
            ]),
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
    private function createToken(): string
    {
        $accessToken = new AccessToken();

        return $accessToken->createToken();
    }
}