<?php

namespace Pipio\Test\Producer;

use Pipio\Producer\Callback;

use PHPUnit\Framework\TestCase;

class CallbackTest extends TestCase {
    public function testCallback() {
        $messages = [];

        $callback = new Callback(
            function ($event, $message) use (&$messages) {
                $messages[] = $message;
            }
        );

        $callback->emit('sandcastle', null);

        $this->assertEquals(1, count($messages));
    }
}
