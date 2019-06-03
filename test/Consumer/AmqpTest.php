<?php

namespace Pipio\Test\Consumer;

use Pipio\Consumer\Amqp;
use Pipio\Test\Sim\AmqpChannel as SimChannel;

use PHPUnit\Framework\TestCase;

class AmqpTest extends TestCase {

    public function getChannel($declare_callback = null, $bind_callback = null, $consume_callback = null) {
        if($declare_callback === null) {
            $declare_callback = function() {};
        }

        if($bind_callback === null) {
            $bind_callback = function() {};
        }

        if($consume_callback === null) {
            $consume_callback = function() {};
        }

        $channel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $channel->expects($this->any())
            ->method('queue_declare')
            ->will($this->returnCallback($declare_callback));

        $channel->expects($this->any())
            ->method('queue_bind')
            ->will($this->returnCallback($bind_callback));

        $channel->expects($this->any())
            ->method('basic_consume')
            ->will($this->returnCallback($consume_callback));

        return $channel;
    }

    public function getConnection() {
        $connection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();
        return $connection;
    }

    public function testOnConfiguresQueue() {
        $declare_called = false;
        $bind_called = false;
        $consume_called = false;

        $declare_callback = function() use (&$declare_called) {
            $declare_called = true;
        };

        $bind_callback = function() use (&$bind_called) {
            $bind_called = true;
        };

        $consume_callback = function () use (&$consume_called) {
            $consume_called = true;
        };

        $channel = $this->getChannel($declare_callback, $bind_callback, $consume_callback);

        $amqp = new Amqp($channel);
        $amqp->on('test.event', 'name');

        $this->assertTrue($declare_called);
        $this->assertTrue($bind_called);
        $this->assertTrue($consume_called);
    }

    public function testOnDoesNotRedeclareQueue() {
        $declared = [];

        $channel = $this->getChannel(function() use (&$declared) { $declared[] = func_get_args(); });

        $amqp = new Amqp($channel);

        $amqp->on('test.event', 'name');
        $amqp->on('test.event', 'name');

        $this->assertEquals(1, count($declared));
    }

    public function testQueueDeclare() {
        $values = [];
        $declare_callback = function() use (&$values) {
            $values = func_get_args();
        };

        $channel = $this->getChannel($declare_callback);

        $amqp = new Amqp($channel);

        $parameters = [
            'queue',
            'passive',
            'durable',
            'exclusive',
            'auto_delete',
            'nowait',
            'arguments',
            'ticket'
        ];
        call_user_func_array([$amqp, 'queueDeclare'], $parameters);

        foreach($parameters as $index => $parameter) {
            $this->assertEquals($values[$index], $parameter);
        }
    }

    public function testQueueBind() {
        $values = [];
        $bind_callback = function() use (&$values) {
            $values = func_get_args();
        };

        $channel = $this->getChannel(null, $bind_callback);

        $amqp = new Amqp($channel);

        $parameters = [
            'queue',
            'exchange',
            '',
            'nowait',
            'arguments',
            'ticket'
        ];

        call_user_func_array([$amqp, 'queueBind'], $parameters);

        foreach($parameters as $index => $parameter) {
            $this->assertEquals($values[$index], $parameter);
        }
    }

    public function testBasicConsumeParameters() {
        $values = [];
        $consume_callback = function() use (&$values) {
            $values = func_get_args();
        };

        $callback = function() {
            return 'string';
        };

        $channel = $this->getChannel(null, null, $consume_callback);

        $amqp = new Amqp($channel);

        $parameters = [
            'queue',
            'consumer_tag',
            'no_local',
            'no_ack',
            'exclusive',
            'nowait',
            $callback,
            'ticket',
            'arguments'
        ];

        call_user_func_array([$amqp, 'basicConsume'], $parameters);

        foreach($parameters as $index => $parameter) {
            if(gettype($values[$index]) == 'object') {
                $this->assertEquals($callback->__invoke(), $parameter->__invoke());
            } else {
                $this->assertEquals($values[$index], $parameter);
            }
        }
    }

    public function testBasicConsumeWithUserCallback() {
        $callback_called = 3;

        $connection = $this->getConnection();

        $channel = new SimChannel($connection);

        $amqp = new Amqp($channel);

        $amqp->basicConsume(
            $queue = '',
            $consumer_tag = '',
            $no_local = false,
            $no_ack = false,
            $exclusive = false,
            $nowait = false,
            $callback = function() use (&$callback_called) {
                $callback_called -= 1;
            }
        );

        $amqp->wait();

        $this->assertEquals(0, $callback_called);
    }

    public function testBasicConsumeDefaultCallback() {
        $connection = $this->getConnection();

        $channel = new SimChannel($connection);

        $amqp = new Amqp($channel);

        $amqp->basicConsume('queue');

        $messages = $amqp->wait();

        $this->assertEquals(3, count($messages));

    }

}