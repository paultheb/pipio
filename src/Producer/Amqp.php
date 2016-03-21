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
            $this->exchangeDeclare($event, self::DEFAULT_EXCHANGE, false, false, false);
        }
        $this->channel->basic_publish($message, $event);
    }

    public function exchangeDeclare($event, $type, $passive, $durable, $auto_delete) {
        return $this->channel->exchange_declare($event, $type, $passive, $durable, $auto_delete);
    }
}
