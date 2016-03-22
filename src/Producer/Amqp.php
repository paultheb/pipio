<?php

namespace Pipio\Producer;

class Amqp implements \Pipio\Producer {

    const DEFAULT_EXCHANGE = 'fanout';

    protected $channel;
    protected $exchanges = [];

    public function __construct(\PhpAmqpLib\Channel $channel) {
        $this->channel = $channel;
    }

    public function emit($event, $message) {
        if(!isset($this->exchanges[$event])) {
            $this->exchanges[$event] = $this->exchangeDeclare($event);
        }
        $this->channel->basic_publish($message, $event);
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
        return $this->channel->exchange_declare($event, $type, $passive, $durable, $auto_delete);
    }
}
