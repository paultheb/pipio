<?php

namespace Pipio\Consumer;
/**
 * @todo Figure out a way to do acks.
 */
class Amqp implements \Pipio\Consumer {

    protected $channel;
    protected $queues = [];
    protected $msgs = [];

    public function __construct(\PhpAmqpLib\Channel\AMQPChannel $channel) {
        $this->channel = $channel;
    }

    public function on($event, $name) {    
        if(!in_array($event, array_keys($this->queues))) {
            $queue_name = $this->queueDeclare($name)[0];
            $this->queueBind($queue_name, $event);
            $this->basicConsume($queue_name);
        } 
        $this->channel->basic_publish($message, $event);
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
        $auto_delete = true,
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        return $this->channel->queue_declare(
            $queue,
            $passive,
            $durable,
            $exclusive,
            $auto_delete,
            $nowait,
            $arguments,
            $ticket
        );
    }

    public function queueBind(
        $queue,
        $exchange,
        $routing_key = '',
        $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        $this->channel->queue_bind($queue, $exchange, '', $nowait, $arguments, $ticket);
        $this->queues[$exchange] = $queue;
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

        $temp_msgs = $this->msgs;
        $default_callback = function($msg) use (&$temp_msgs) {
            $temp_msgs[] = $msg;
        };

        $this->msgs = $temp_msgs;

        if($callback != null) {
            $user_callback = $callback;

            $callback = function($msg) use ($default_callback, $user_callback) {
                $user_callback($msg);
                $default_callback($msg);
            };


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
