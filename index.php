<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MozoDev\LetsEncrypt\Program;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$program = new Program();

if(isset($argv[1]) && $argv[1] === 'cleanup') {
    $program->cleanup();
    die(0);
}

$program->execute();