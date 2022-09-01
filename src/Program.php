<?php

namespace MozoDev\LetsEncrypt;

use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Program
{
    private DnsRecordUpdater $dnsRecordUpdater;

    public function __construct()
    {
        $this->dnsRecordUpdater = new DnsRecordUpdater();
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function execute(): void
    {
        $result = $this->dnsRecordUpdater->updateRecord() ? 'DONE' : 'FAILED';
        echo "Renewal status: {$result}\n";
    }
}