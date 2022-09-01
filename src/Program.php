<?php

namespace MozoDev\LetsEncrypt;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class Program
{
    private DnsRecordUpdater $dnsRecordUpdater;
    public static Logger $logger;

    public function __construct()
    {
        $this->dnsRecordUpdater = new DnsRecordUpdater();

        // Setup logger
        self::$logger = new Logger('LetsEncrypt');
        self::$logger->pushHandler(new StreamHandler(__DIR__ . '../letsencrypt.log', Level::fromName($_ENV['LOGGING_LEVEL'])));
        self::$logger->pushHandler(new StreamHandler(STDOUT, Level::fromName($_ENV['LOGGING_LEVEL'])));
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

        $result = $this->dnsRecordUpdater->updateRecord() ? 'DONE' : 'FAILED';
        self::$logger->info("Renewal status: {$result}\n");
    }
}