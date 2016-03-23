<?php

namespace Pipio\test\Sim;

use PhpAmqpLib\Message\AMQPMessage;

class AmqpChannel extends \PhpAmqpLib\Channel\AMQPChannel {

    protected $callback;

    protected $messages;

    public function __construct($connection) {
        $this->messages = [
            new AMQPMessage('message 1'),
            new AMQPMessage('message 2'),
            new AMQPMessage('message 3')
        ];

        foreach($this->messages as $message) {
            $message->delivery_info['exchange'] = 'test.test';
        }
    }

    public function wait($allowed_methods = null, $non_blocking = false, $timeout = 0) {

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
