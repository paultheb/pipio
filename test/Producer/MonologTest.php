<?php

namespace Pipio\Test\Producer;

use Pipio\Producer\Monolog;

class MonologTest extends \PHPUnit_Framework_TestCase {
    public function testMonologLog() {
        $event = 'potato';
        $message = 'tomato';

        $path = tempnam(sys_get_temp_dir(), 'Temp');

        $log = new \Monolog\Logger('test');
        $log->pushHandler(new \Monolog\Handler\StreamHandler($path));

        $monolog = new Monolog($log);

        $monolog->emit($event, $message);

        $this->assertNotEmpty(file_get_contents($path));

    }
}
