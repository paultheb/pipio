<?php

namespace Pipio\Producer;

class Filter implements \Pipio\Producer {
    protected $filter;
    protected $producer;

    public function __construct($filter, \Pipio\Producer $producer) {
        $this->filter = $filter;
        $this->producer = $producer;
    }

    public function emit($event, $message) {
        if(preg_match($this->filter, $event)) {
            $this->producer->emit($event, $message);
        }
    }
}
