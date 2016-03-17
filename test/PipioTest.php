<?php

namespace Pipio\Test\Pipio;

use Pipio\Pipio;
use Pipio\Test\Sim\Pipio as SimPipio;

class PipioTest extends \PHPUnit_Framework_TestCase {
    public function testCallOverrideThrowsExceptionOnBadMethod() {
        $this->setExpectedException('BadMethodCallException');

        $pipio = new Pipio();

        $pipio->methodDoesNotExist();
    }

    public function testCallOverrideThrowsExceptionOnBadEmitCall() {
        $this->setExpectedException('InvalidArgumentException');

        $pipio = new Pipio();

        $pipio->emitTestEvent();
    }

    public function testCallOverrideThrowsExceptionOnBadOnCall() {
        $this->setExpectedException('InvalidArgumentException');

        $pipio = new Pipio();

        $pipio->onTestEvent(3, 7, 9);
    }

    public function testOnThrowsExceptionOnOverflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new Pipio();
        $pipio->on('Test', str_repeat('a', 256), function ($event, $message) {});
    }

    public function testOnThrowsExceptionOnUnderflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new Pipio();
        $pipio->on('Test', '', function ($event, $message) {});
    }

    public function testOnDoesNotAcceptDuplicateListeners() {
        $this->setExpectedException('InvalidArgumentException');

        $pipio = new Pipio();
        $pipio->on('Test', 'Test', function($event, $message) {});
        $pipio->on('Test', 'Test', function($event, $message) {});
    }

    public function testOnCreatesValidName() {
        $pipio = new Pipio();

        $name = $pipio->on('Test', null, function($event, $message) {});

        $this->assertEquals(strlen($name), 32);

        $name = $pipio->on('Test', 'TestTest', function($event, $message) {});

        $this->assertEquals($name, 'test.test');
    }

    public function testRemoveListener() {
        $pipio = new Pipio();

        $event = 'SomeEvent';
        $name = 'SomeName';

        $this->assertFalse($pipio->removeListener($event, $name));

        $pipio->on($event, $name, function($event, $message) {});

        $this->assertTrue($pipio->removeListener($event, $name));
    }

    public function testHasListeners() {
        $pipio = new Pipio();

        $name = 'SomeEvent';

        $this->assertFalse($pipio->hasListeners($name));

        $listener = $pipio->on($name, null, function($event, $message) {});

        $this->assertTrue($pipio->hasListeners($name));

        $pipio->removeListener($name, $listener);

        $this->assertFalse($pipio->hasListeners($name));
    }

    public function testEmit() {
        $pipio = new Pipio();
        $pipio->emit('Test', 'Test message');
        $pipio->on('Test', 'test', function($event, $message) {});
        $pipio->emit('Test', 'Test message');
    }

    public function testWaitTimeout() {
        $pipio = new Pipio();
        $pipio->on('test', 'test', function($event, $message) {});

        $before = time();

        $pipio->wait(0);

        $this->assertLessThan(1, time() - $before);

        $pipio->setTimeout(15);

        $pipio->wait();

        $this->assertLessThan(16, time() - $before);
        $this->assertGreaterThan(14, time() - $before);
    }

    public function testPipio() {
        $messages = [];

        $pipio = new Pipio();
        $pipio->on(
            'SomeEvent',
            null,
            function($event, $message) use ($pipio) {
                $pipio->emit('OtherEvent', 'Hello');
            }
        );
        $pipio->on(
            'OtherEvent',
            null,
            function($event, $message) use (&$messages) {
                $messages[] = $message;
            }
        );

        $pipio->emit('SomeEvent');
        $pipio->emit('OtherEvent', 'Goodbye');

        $pipio->wait(0);

        $this->assertEquals(count($messages), 2);

        $this->assertTrue(in_array('Hello', $messages));
        $this->assertTrue(in_array('Goodbye', $messages));
    }


    public function testConvertEventDescriptorThrowsExceptionOnOverflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new SimPipio();

        $pipio->testConvertEventDescriptor(str_repeat('a', 256));
    }

    public function testConvertEventDescriptorThrowsExceptionUnderflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new SimPipio();

        $pipio->testConvertEventDescriptor('');
    }

    public function testConvertEventDescriptor() {
        $cases = [
            'test' => 'test',
            'test12345' => 'test',
            'test.test' => 'test.test',
            'testTest' => 'test.test',
            '.test' => 'test',
            'test.' => 'test',
            'test.Test' => 'test.test',
            'TESTTest' => 'test.test',
            'TESTTEST' => 'testtest',
            ']TEST' => 'test',
            'test-test' => 'test.test',
            'test\test' => 'test.test',
            'TEST\test' => 'test.test',
            'test\TEST' => 'test.test',
            'Test\Test' => 'test.test'
        ];

        $pipio = new SimPipio();

        foreach($cases as $pass => $expect) {

            $this->assertEquals($expect, $pipio->testConvertEventDescriptor($pass));
        }
    }
}
