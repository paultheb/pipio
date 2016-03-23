<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Pipio\Pipio;

list($script, $host, $port, $user, $pass, $event, $message) = $argv;

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
    $host,
    $port,
    $user,
    $pass
);

$channel = $connection->channel();

$pipio = new Pipio();

$pipio->addProducer(new \Pipio\Producer\Amqp($channel));

$pipio->emit($event, $message);

$connection->close();
