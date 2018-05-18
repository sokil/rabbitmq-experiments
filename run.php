#!/usr/bin/env php
<?php

require_once './vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new \Command\FanoutCommand("fanout"));

$app->run();