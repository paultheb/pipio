<?php

namespace Pipio;

class Pipio {
    const DESCRIPTOR_LIMIT = 255;
    const DEFAULT_TIMEOUT = 30;

    protected $channel;
    protected $logger;
    protected $timeout;
    protected $listeners;
    protected $count_listeners;
    protected $events;

    public function __construct($channel = null, $logger = null) {
        $this->channel = $channel;
        $this->logger = $logger;

        $this->listeners = [];
        $this->events = [];
        $this->count_listeners = 0;

        $this->setTimeout(self::DEFAULT_TIMEOUT);
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

    public function emit($event, $message) {
        if($this->hasListeners($event)) {
            $this->events[] = [$this->convertEventDescriptor($event), $message];
        }
    }

    public function on($event, $name = null, callable $callback) {
        return $this->addListener($event, $name, $callback);
    }

    public function wait(int $timeout = null) {
        if($timeout !== null) {
            $this->setTimeout($timeout);
        }

        $continue = true;

        $last = time();

        while($continue) {
            if(count($events)) {
                list($event, $message) = array_shift($events);

                $listeners = $this->listeners[$event];

                foreach($listeners as $listener) {
                    $listener($event, $message);

                    $last = time();
                }
            }

            $continue = ($last + $this->timeout > time()) && $this->count_listeners != 0;
        }
    }

    public function addListener($event, $name = null, callable $callback) {
        $event = $this->convertEventDescriptor($event);

        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        if(isset($this->listeners[$event][$name])) {
            throw new \InvalidArgumentException('Cannot add named listener. A listener with that name already exists.');
        }

        while($name === null || isset($this->listeners[$event][$name])) {
            $name = $this->generateName($event);
        }

        $name = $this->convertEventDescriptor($name);

        $this->listeners[$event][$name] = $callback;

        $this->count_listeners++;

        return $name;
    }

    public function removeListener($event, $name) {
        if(!isset($this->listeners[$event])) {
            return false;
        }

        if(!isset($this->listeners[$event][$name])) {
            return false;
        }

        unset($this->listeners[$event][$name]);

        $this->count_listeners--;

        return true;
    }

    public function hasListeners($event) {
        return isset($this->listeners[$event]) && (count($this->listeners[$event]) > 0);
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    protected function convertEventDescriptor($descriptor) {
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

    protected function generateName($event) {
        $valid = array_flip(str_split('abcdefghijklmnopqrstuvwxyz'));
        $length = 32;
        $name = '';

        while($length > 0) {
            $name .= array_rand($valid);

            $length--;
        }

        return $name;
    }
}
