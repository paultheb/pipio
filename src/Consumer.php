<?php

namespace Pipio;

interface Consumer {
    public function on($event, $name);
    public function wait();
}
