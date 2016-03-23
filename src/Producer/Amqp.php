<?php

namespace Pipio\Producer;

use PhpAmqpLib\Message\AMQPMessage;

class Amqp implements \Pipio\Producer {

    const DEFAULT_EXCHANGE = 'fanout';

    protected $channel;
    protected $exchanges = [];

    public function __construct(\PhpAmqpLib\Channel\AMQPChannel $channel) {
        $this->channel = $channel;
    }

    public function emit($event, $message) {
        if(!in_array($event, array_keys($this->exchanges))) {
            $this->exchangeDeclare($event);
        }

        $this->channel->basic_publish(new AMQPMessage($message), $event);
    }

    public function exchangeDeclare(
        $event,
        $type = self::DEFAULT_EXCHANGE,
        $passive = false,
        $durable = false,
        $auto_delete = false,
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {

        $this->exchanges[$event] = $this->channel->exchange_declare(
            $event,
            $type,
            $passive,
            $durable,
            $auto_delete,
            $nowait,
            $arguments,
            $ticket
        );

        return $this->exchanges[$event];
    }
}
