<?php

namespace Pipio\Test;

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

    public function testEmitNoListener() {
        $pipio = new Pipio();
        $pipio->emit('Test', 'Test message');
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

    public function testOnCreatesValidName() {
        $pipio = new Pipio();

        $name = $pipio->on('Test', null, function($event, $message) {});

        $this->assertEquals(strlen($name), 32);

        $name = $pipio->on('Test', 'TestTest', function($event, $message) {});

        $this->assertEquals($name, 'test.test');
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
