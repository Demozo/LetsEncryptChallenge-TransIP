<?php

namespace MozoDev\LetsEncrypt\TransIP;

use Exception;

/**
 * A class for generating an Access Token.
 *
 * @author: TransIP <support@transip.nl>
 * @version 1.1
 */
class AccessToken
{
    /**
     * Your login name on the TransIP website.
     *
     * @var string
     */
    private readonly string $login;

    /**
     * One of your private keys; these can be requested via your Controlpanel
     *
     * @var string
     */
    private readonly string $privateKey;

    /**
     * The URL for authentication, this should be formatted with the endpoint URL
     */
    const AUTH_URL = 'https://%s/%s/auth';

    /**
     * TransIP API endpoint to connect to.
     *
     * e.g.:
     *
     *        'api.transip.nl'
     *        'api.transip.be'
     *        'api.transip.eu'
     *
     * @var string
     */
    const ENDPOINT = 'api.transip.nl';

    /**
     * API version number
     *
     * @var string
     */
    const VERSION = 'v6';

    /**
     * Read only mode
     * @var bool
     */
    const READ_ONLY = false;

    /**
     * Whether no whitelisted IP address is needed.
     * Set to true when you want to be able to use a token from anywhere
     * @var string
     */
    const GLOBAL_KEY = false;

    /**
     * Default expiration time.
     * The maximum expiration time is one month.
     * @var string
     */
    private readonly string $expirationTime;

    /**
     * The label for the new access token
     */
    private string $label;

    private string $signature;

    public function __construct()
    {
        $this->login = $_ENV['TRANSIP_LOGIN'];
        $this->privateKey = $_ENV['TRANSIP_KEY'];
        $this->expirationTime = $_ENV['TRANSIP_TOKEN_EXPIRATION'];
        $this->label = $_ENV['TRANSIP_TOKEN_LABEL'];
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Creates a new Access Token
     *
     * @return string
     * @throws Exception
     */
    public function createToken(): string
    {
        $requestBody = $this->getRequestBody();

        // Create signature using the JSON encoded request body and your private key.
        $this->signature = $this->createSignature($requestBody);

        $responseJson = $this->performRequest($requestBody);

        if (!isset($responseJson->token)) {
            throw new \RuntimeException("An error occurred: {$responseJson->error}");
        }

        return $responseJson->token;
    }

    /**
     * @return string
     */
    private function getAuthUrl(): string
    {
        return sprintf(self::AUTH_URL, self::ENDPOINT, self::VERSION);
    }

    /**
     * Creates a JSON encoded string of the request body
     *
     * @return string
     */
    private function getRequestBody(): string
    {
        $labelSuffix = floor(microtime(true) * 1000);

        $requestBody = [
            'login'             => $this->login,
            'nonce'             => uniqid(),
            'read_only'         => self::READ_ONLY,
            'expiration_time'   => $this->expirationTime,
            'label'             => "{$this->label}-{$labelSuffix}",
            'global_key'        => self::GLOBAL_KEY,
        ];
        return json_encode($requestBody);
    }

    /**
     * @param string $requestBody
     * @return ?object
     */
    private function performRequest(string $requestBody): ?object
    {
        // Set up CURL request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getAuthUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Signature: ' . $this->signature
            ]
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    /**
     * Method for creating a signature based on
     * Same sign method as used in SOAP API.
     *
     * @param string $parameters
     * @return string
     * @throws Exception
     */
    private function createSignature(string $parameters): string
    {
        // Fixup our private key, copy-pasting the key might lead to whitespace faults
        if (!preg_match(
            '/-----BEGIN (RSA )?PRIVATE KEY-----(.*)-----END (RSA )?PRIVATE KEY-----/si',
            $this->privateKey,
            $matches
        )
        ) {
            throw new \RuntimeException('Could not find a valid private key');
        }

        $key = $matches[2];
        $key = preg_replace('/\s*/s', '', $key);
        $key = chunk_split($key, 64, "\n");

        $key = "-----BEGIN PRIVATE KEY-----\n" . $key . "-----END PRIVATE KEY-----";

        if (!@openssl_sign($parameters, $signature, $key, OPENSSL_ALGO_SHA512)) {
            throw new \RuntimeException(
                'The provided private key is invalid'
            );
        }

        return base64_encode($signature);
    }
}
