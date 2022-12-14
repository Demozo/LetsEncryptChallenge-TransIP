<?php

namespace MozoDev\LetsEncrypt;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class Program
{
    private DnsRecordUpdater $dnsRecordUpdater;
    private WellKnownUpdater $wellKnownUpdater;
    public static Logger $logger;

    public function __construct()
    {
        $this->dnsRecordUpdater = new DnsRecordUpdater();
        $this->wellKnownUpdater = new WellKnownUpdater();

        // Setup logger
        self::$logger = new Logger('LetsEncrypt');

        $fileHandler = new StreamHandler(__DIR__ . '/../letsencrypt.log', Level::fromName($_ENV['LOGGING_LEVEL']));
        $fileHandler->setFormatter(new LineFormatter(allowInlineLineBreaks: true));
        self::$logger->pushHandler($fileHandler);

        ErrorHandler::register(self::$logger);
    }

    public function __destruct()
    {
        self::$logger->close();
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(): void
    {
        self::$logger->debug(json_encode($_SERVER, JSON_PRETTY_PRINT));

        if (array_key_exists('CERTBOT_TOKEN', $_SERVER)) {
            self::$logger->info('Starting .well-known update');
            $result = $this->wellKnownUpdater->updateWellKnown() ? 'DONE' : 'FAILED';

            // Echo CERTBOT_TOKEN, so we can delete the right file in the cleanup step
            echo $_SERVER['CERTBOT_TOKEN'];
        } else {
            self::$logger->info('Starting DNS update');
            $result = $this->dnsRecordUpdater->updateRecord() ? 'DONE' : 'FAILED';

            // Echo CERTBOT_VALIDATION, so we can match the DNS record and delete it in the cleanup step
            echo $_SERVER['CERTBOT_VALIDATION'];
        }

        self::$logger->info("Renewal status: {$result}");
    }

    public function cleanup(): void
    {
        self::$logger->info('Beginning cleanup procedure');

        if (array_key_exists('CERTBOT_TOKEN', $_SERVER)) {
            $wellKnownResult = $this->wellKnownUpdater->cleanup() ? 'DONE' : 'FAILED';
            self::$logger->info("Cleaning .well-known status: {$wellKnownResult}");
        } else {
            $dnsResult = $this->dnsRecordUpdater->cleanup() ? 'DONE' : 'FAILED';
            self::$logger->info("Cleaning DNS status: {$dnsResult}");
        }
    }
}