<?php

namespace Pipio\test\Sim;

class AmqpChannel extends \PhpAmqpLib\Channel\AMQPChannel {

    protected $callback;

    protected $messages = [
        'message 1',
        'message 2',
        'message 3'
    ];

    public function __construct($connection) {

    }

    public function wait() {

        foreach($this->messages as $message) {
            $callback = $this->callback;
            $callback($message);
        }

    }

    public function basic_consume(
        $queue = '',
        $consumer_tag = '',
        $no_local = false,
        $no_ack = false,
        $exclusive = false,
        $nowait = false,
        $callback = null,
        $ticket = null,
        $arguments = array()
    ) {
        $this->callback = $callback;
    }

    public function getMessages() {
        return $this->messages;
    }

}