<?php

namespace Pipio\Test\Producer;

use Pipio\Producer\Monolog;

class MonologTest extends \PHPUnit_Framework_TestCase {
    public function testMonologLog() {
        $event = 'potato';
        $message = 'tomato';

        $temp_log_file = tmpfile();

        $log = new \Monolog\Logger('test');
        $log->pushHandler(new \Monolog\StreamHandler($temp_log_file));

        $monolog = new Monolog($log);

        $monolog->emit($event, $message);

        $this->assertFileExists($temp_log_file);

        $this->assertNotEmpty(file_get_contents($temp_log_file));

    }
}
