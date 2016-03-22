<?php

namespace Pipio\Test\Consumer;

use Pipio\Consumer\Amqp;

class AmqpTest extends \PHPUnit_Framework_TestCase {

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

    public function testOnDeclaresQueue() {
        $called = false;
        $channel = $this->getChannel(function() use (&$called) {
            $called = true;
        }, null, null);
        $amqp = new Amqp($channel);
        $amqp->on('event', 'name');
        $this->assertTrue($called);
    }

    public function testOnBindsQueue() {
        $called = false;
        $channel = $this->getChannel(null, function() use (&$called) {
            $called = true;
        }, null);
        $amqp = new Amqp($channel);
        $amqp->on('event', 'name');
        $this->assertTrue($called);
    }

    public function testOnCallsBasicConsume() {
        $called = false;
        $channel = $this->getChannel(null, null, function() use (&$called) {
            $called = true;
        });
        $amqp = new Amqp($channel);
        $amqp->on('event', 'name');
        $this->assertTrue($called);
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

    public function testBasicConsume() {
        $values = [];
        $consume_callback = function() use (&$values) {
            $values = func_get_args();
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
            function() {},
            'ticket',
            'arguments'
        ];

        call_user_func_array([$amqp, 'basicConsume'], $parameters);

        foreach($parameters as $index => $parameter) {
            $this->assertEquals($values[$index], $parameter);
        }
    }

}