<?php

namespace Pipio;

interface Consumer {
    public function on($event, $name, $message);
    public function wait(&$events);
}
