<?php

namespace Pipio\Producer;

class Callback implements \Pipio\Producer {
    protected $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function emit($event, $message) {
        $callback = $this->callback;

        $callback($event, $message);
    }
}
