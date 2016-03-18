<?php

namespace Pipio\Test\Producer;

use Pipio\Test\Sim\Logger as SimLogger;

class LoggerTest extends \PHPUnit_Framework_TestCase {

    public function testLoggerAcceptsNonArrayMessage() {
        $sim_logger = new SimLogger();
        $logger = new \Pipio\Producer\Logger($sim_logger);
        $logger->emit('test', 'Omniknight');
        $this->assertEquals('test', $sim_logger->getLastLog()[1]);
    }

    public function testLoggerAcceptsNullMessage() {
        $sim_logger = new SimLogger();
        $logger = new \Pipio\Producer\Logger($sim_logger);
        $logger->emit('test', null);
        $this->assertEquals($logger::DEFAULT_LOG_LEVEL, $sim_logger->getLastLog()[0]);
    }

    public function testLoggerAcceptsNullEmptyArrayMessage() {
        $sim_logger = new SimLogger();
        $logger = new \Pipio\Producer\Logger($sim_logger);
        $logger->emit('test', []);
        $this->assertEquals($logger::DEFAULT_LOG_LEVEL, $sim_logger->getLastLog()[0]);
    }

    public function testLogLevels() {
        $bad_log_level = 'Pudge';
        $good_log_levels = [];
        $sim_logger = new SimLogger();
        $logger = new \Pipio\Producer\Logger($sim_logger);

        foreach($good_log_levels as $log_level) {
            $logger->emit('test', [$logger::LOG_LEVEL => $log_level]);
            $this->assertEquals($log_level, $sim_logger->getLastLog()[0]);
        }

        $logger->emit('test', [$logger::LOG_LEVEL => $bad_log_level]);
        $this->assertEquals('info', $sim_logger->getLastLog()[0]);
    }

}
