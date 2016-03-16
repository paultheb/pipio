<?php

namespace Pipio;

class Pipio {
    const DESCRIPTOR_LIMIT = 255;

    protected $channel;
    protected $logger;


    public function __construct($channel = null, $logger = null) {
        $this->channel = $channel;
        $this->logger = $logger;
    }

    public function emit($event, $message) {

    }

    public function on($event, $name = null, callback $callback) {

    }

    public function wait($timeout) {

    }

    public function __call($name, array $arguments) {
        if(strpos($name, 'emit') === 0) {
            $event = substr($name, 4);

            if(count($arguments) != 1) {
                throw new \InvalidArgumentException('Invalid overload for __call. Pipio::emit expects one argument.');
            }

            return $this->emit($event, $arguments[0]);
        } elseif(strpos($name, 'on') === 0) {
            $event = substr($name, 2);

            if(count($arguments) != 2) {
                throw new \InvalidArgumentException('Invalid overload for __call. Pipio::on expects two arguments.');
            }

            return $this->on($event, $arguments[0], $arguments[1]);
        }

        throw new \BadMethodCallException('Undefined overload for _call: ' . $name);
    }

    public function convertEventDescriptor($descriptor) {
        $descriptor = str_replace('\\', '.', $descriptor);
        $descriptor = preg_replace('/[-_\|]/', '.', $descriptor);
        $descriptor = preg_replace('/[^a-zA-Z\.]/', '', $descriptor);
        $descriptor = preg_replace('/([A-Z]*)([A-Z])([a-z])/', '\1.\2\3', $descriptor);
        $descriptor = preg_replace('/\.+/', '.', $descriptor);
        $descriptor = strtolower($descriptor);

        $descriptor = trim($descriptor, '.');

        if(strlen($descriptor) > self::DESCRIPTOR_LIMIT || strlen($descriptor) === 0) {
            throw new \OutOfBoundsException('Descriptor name cannot be longer than 255 characters.');
        }

        return $descriptor;
    }
}
