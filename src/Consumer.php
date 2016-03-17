<?php

namespace Pipio;

interface Consumer {
    public function addListener($event, $name);
    public function removeListener($event, $name);
    public function wait();
}
