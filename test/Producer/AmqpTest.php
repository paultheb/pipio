<?php

namespace Pipio\Test\Producer;

use Pipio\Producer\Amqp;

class AmqpTest extends \PHPUnit_Framework_TestCase {
    public function getChannel($declare_callback = null, $publish_callback = null) {
        if($declare_callback === null) {
            $declare_callback = function() {};
        }

        if($publish_callback === null) {
            $publish_callback = function() {};
        }

        $channel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $channel->expects($this->any())
            ->method('exchange_declare')
            ->will($this->returnCallback($declare_callback));

        $channel->expects($this->any())
            ->method('basic_publish')
            ->will($this->returnCallback($publish_callback));

        return $channel;
    }

    public function testEmitPublishes() {
        $called = false;

        $channel = $this->getChannel(null, function() use (&$called) { $called = true; });

        $amqp = new Amqp($channel);
        $amqp->emit('event', 'message');

        $this->assertTrue($called);
    }

    public function testExchangeDeclare() {
        $values = [];
        $declare_callback = function() use (&$values) {
            $values = func_get_args();
        };

        $channel = $this->getChannel($declare_callback);

        $amqp = new Amqp($channel);

        $parameters = [
            'event',
            'fanout',
            'a',
            'b',
            'c',
            'd',
            'e',
            'f'
        ];

        call_user_func_array([$amqp, 'exchangeDeclare'], $parameters);

        foreach($parameters as $index => $parameter) {
            $this->assertEquals($values[$index], $parameter);
        }
    }

    public function testExchangeNotRedclared() {
        $declared = [];

        $channel = $this->getChannel(function() use (&$declared) { $declared[] = func_get_args(); });

        $amqp = new Amqp($channel);
        $amqp->emit('event', 'message');
        $amqp->emit('event', 'message');

        $this->assertEquals(1, count($declared));
    }


}