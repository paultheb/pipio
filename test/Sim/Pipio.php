<?php

namespace Pipio\Test\Sim;

class Pipio extends \Pipio\Pipio {
    public function testConvertEventDescriptor($descriptor) {
        return $this->convertEventDescriptor($descriptor);
    }
}
