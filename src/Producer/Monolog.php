<?php

namespace Pipio\Producer;

class Monolog implements \Pipio\Producer {

    private $log;

    public function __construct($log) {
        $this->log = $log
    }

    public function emit($event, $message) {
        $log->addInfo('EVENT: ' . $event . ' MESSAGE: ' . json_encode($message));
    }

}
