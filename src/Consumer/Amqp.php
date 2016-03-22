<?php

namespace Pipio\Consumer;

class Amqp implements \Pipio\Consumer {

    protected $channel;
    protected $queues = [];
    protected $msgs = [];

    public function __construct(\PhpAmqpLib\Channel $channel) {
        $this->channel = $channel;
    }

    public function on($event, $name) {
        if(!isset($this->queues[$name])) {
            $this->queues[$name] = $name;
        }

        list($queue_name, ,) = $this->queueDeclare($name);

        $this->queueBind($queue_name, $event);

        $this->basicConsume($queue_name);
    }

    public function wait() {
        $channel->wait();

        $messages = [];

        while(count($this->msgs)) {
            $messages[] = array_shift($this->msgs);
        }

        return $messages;
    }

    public function queueDeclare(
        $queue = '',
        $passive = false,
        $durable = false,
        $exclusive = false,
        $auto_delete = false
    ) {
        return $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete);
    }

    public function queueBind(
        $queue,
        $exchange,
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        $this->channel->queue_bind($queue, $exchange, '', $nowait, $arguments, $ticket);
    }

    public function basicConsume(
        $queue = '',
        $consumer_tag = '',
        $no_local = false,
        $no_ack = false,
        $exclusive = false,
        $nowait = false,
        $callback = null,
        $ticket = null,
        $arguments = []
    ) {
        $default_callback = function($msg) use (&$this->msgs) {
            $this->msgs[] = $msg;
        });

        if($callback != null) {
            $user_callback = $callback;

            $callback = function($msg) use ($default_callback) {
                $user_callback($msg);
                $default_callback($msg);
            }


        } else {
            $callback = $default_callback;
        }

        $this->channel->basic_consume(
            $queue,
            $consumer_tag,
            $no_local,
            $no_ack,
            $exclusive,
            $nowait,
            $callback,
            $ticket,
            $arguments
        );
    }
}
