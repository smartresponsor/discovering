<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$discoveryStateDirectory = dirname(__DIR__).'/var/test';
if (!is_dir($discoveryStateDirectory) && !mkdir($discoveryStateDirectory, 0775, true) && !is_dir($discoveryStateDirectory)) {
    throw new RuntimeException(sprintf('Unable to create discovery test state directory "%s".', $discoveryStateDirectory));
}

if (class_exists(Dotenv::class) && file_exists(dirname(__DIR__).'/.env')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
