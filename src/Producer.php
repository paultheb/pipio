<?php

namespace Pipio;

interface Producer {
    public function emit($event, $message);
}
