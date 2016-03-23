<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Pipio\Pipio;

list($script, $host, $port, $user, $pass, $event) = $argv;

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
    $host,
    $port,
    $user,
    $pass
);

$channel = $connection->channel();

$pipio = new Pipio();

$pipio->addConsumer(new \Pipio\Consumer\Amqp($channel));

$pipio->on($event, null, function($message) { echo $message; });

$pipio->wait();

$connection->close();
