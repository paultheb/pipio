<?php

namespace Pipio\Test;

use Pipio\Pipio;
use Pipio\Test\Sim\Pipio as SimPipio;

class PipioTest extends \PHPUnit_Framework_TestCase {
    public function testCallOverrideThrowsExceptionOnBadMethod() {
        $this->setExpectedException('BadMethodCallException');

        $pipio = new SimPipio();

        $pipio->methodDoesNotExist();
    }

    public function testCallOverrideThrowsExceptionOnBadEmitCall() {
        $this->setExpectedException('InvalidArgumentException');

        $pipio = new SimPipio();

        $pipio->emitTestEvent();
    }

    public function testCallOverrideThrowsExceptionOnBadOnCall() {
        $this->setExpectedException('InvalidArgumentException');

        $pipio = new SimPipio();

        $pipio->onTestEvent(3, 7, 9);
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
