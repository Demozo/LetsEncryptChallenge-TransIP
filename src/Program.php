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
    public static Logger $logger;

    public function __construct()
    {
        $this->dnsRecordUpdater = new DnsRecordUpdater();

        // Setup logger
        self::$logger = new Logger('LetsEncrypt');

        $fileHandler = new StreamHandler(__DIR__ . '/../letsencrypt.log', Level::fromName($_ENV['LOGGING_LEVEL']));
        $fileHandler->setFormatter(new LineFormatter(allowInlineLineBreaks: true));
        self::$logger->pushHandler($fileHandler);

        $stdOutHandler = new StreamHandler(STDOUT, Level::fromName($_ENV['LOGGING_LEVEL']));
        $stdOutHandler->setFormatter(new LineFormatter(allowInlineLineBreaks: true));
        self::$logger->pushHandler($stdOutHandler);

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