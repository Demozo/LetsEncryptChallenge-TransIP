<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MozoDev\LetsEncrypt\Program;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$program = new Program();
$program->execute();