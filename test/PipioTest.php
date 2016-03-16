<?php

namespace Pipio\Test;

use Pipio\Pipio;

class PipioTest extends \PHPUnit_Framework_TestCase {
    public function testConvertEventDescriptorThrowsExceptionOnOverflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new Pipio();

        $pipio->convertEventDescriptor(str_repeat('a', 256));
    }

    public function testConvertEventDescriptorThrowsExceptionUnderflowLength() {
        $this->setExpectedException('OutOfBoundsException');

        $pipio = new Pipio();

        $pipio->convertEventDescriptor('');
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

        $pipio = new Pipio();

        foreach($cases as $pass => $expect) {

            $this->assertEquals($expect, $pipio->convertEventDescriptor($pass));
        }
    }
}
