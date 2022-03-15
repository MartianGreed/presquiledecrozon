<?php
declare(strict_types=1);

use Bref\Symfony\Messenger\Service\Sqs\SqsConsumer;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();

// Return the Bref consumer service
return $kernel->getContainer()->get(SqsConsumer::class);