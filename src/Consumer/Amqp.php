<?php

namespace Pipio\Consumer;

class Amqp implements \Pipio\Consumer {

    protected $channel;

    public function __construct(\PhpAmqpLib\Channel $channel) {
        $this->channel = $channel;
    }

    public function on() {

    }

    public function wait() {

    }
}
