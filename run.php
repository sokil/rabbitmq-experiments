#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPLazySocketConnection;
use Symfony\Component\Console\Application;

// init configuration

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/docker/.env');

// init dependencies
$socketConnection = new AMQPLazySocketConnection(
    'haproxy',
    getenv('RABBITMQ_CLUSTER_PORT'),
    getenv('RABBITMQ_DEFAULT_USER'),
    getenv('RABBITMQ_DEFAULT_PASS')
);

// create console application
$app = new Application();

$app->add(new \Command\FanoutCommand(
    "fanout",
    $socketConnection
));

$app->add(new \Command\FanoutVsTopicBenchCommand(
    "fanoutVsTopic",
    $socketConnection
));

// run console application
$app->run();
