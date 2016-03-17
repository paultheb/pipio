<?php

namespace Pipio\Test\Producer;

use Pipio\Producer\Filter;
use Pipio\Producer\Callback;

class FilterTest extends \PHPUnit_Framework_TestCase {
    public function testFilter() {
        $messages = [];

        $filter = new Filter(
            '/potato/',
            new Callback(
                function ($event) use (&$messages) {
                    $messages[] = 'called';
                }
            )
        );

        $filter->emit('sandcastle', null);

        $this->assertEmpty($messages);

        $filter->emit('potato', null);

        $this->assertEquals(1, count($messages));
    }
}
