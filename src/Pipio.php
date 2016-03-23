<?php

namespace Pipio;

class Pipio {
    const DESCRIPTOR_LIMIT = 255;
    const DEFAULT_TIMEOUT = 30;
    const DEFAULT_TICK = 0;

    protected $timeout;
    protected $tick;
    protected $listeners;
    protected $count_listeners;
    protected $events;
    protected $producers;
    protected $consumers;

    public function __construct() {
        $this->listeners = [];
        $this->events = [];
        $this->producers = [];
        $this->consumers = [];
        $this->count_listeners = 0;

        $this->setTimeout(self::DEFAULT_TIMEOUT);
        $this->setTick(self::DEFAULT_TICK);
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

    public function addProducer(Producer $producer) {
        $this->producers[] = $producer;
    }

    public function addConsumer(Consumer $consumer) {
        $this->consumers[] = $consumer;
    }

    public function emit($event, $message = null) {
        if($this->hasListeners($event)) {
            $this->events[] = [$this->convertEventDescriptor($event), $message];
        }

        foreach($this->producers as $producer) {
            $producer->emit($event, $message);
        }
    }

    public function on($event, $name = null, callable $callback) {
        return $this->addListener($event, $name, $callback);
    }

    public function wait($timeout = null) {
        if($timeout !== null) {
            $this->setTimeout($timeout);
        }

        $continue = true;

        $last = time();

        while($continue) {
            foreach($this->consumers as $consumer) {
                foreach($consumer->wait() as $event) {
                    $event = [$this->convertEventDescriptor($event[0]), $event[1]];

                    $this->events[] = $event;
                }
            }

            if(count($this->events) > 0) {
                list($event, $message) = array_shift($this->events);

                $listeners = $this->listeners[$event];

                foreach($listeners as $listener) {
                    $listener($event, $message);

                    $last = time();
                }
            }

            sleep($this->tick);

            $continue = count($this->events) > 0 || ($last + $this->timeout > time()) && $this->count_listeners != 0;
        }
    }

    public function addListener($event, $name = null, callable $callback) {
        $event = $this->convertEventDescriptor($event);

        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        if($name !== null) {
            $name = $this->convertEventDescriptor($name);
        }

        if(isset($this->listeners[$event][$name])) {
            throw new \InvalidArgumentException('Cannot add named listener. A listener with that name already exists.');
        }

        while($name === null || isset($this->listeners[$event][$name])) {
            $name = $this->generateName($event);
        }

        foreach($this->consumers as $consumer) {
            $consumer->on($event, $name);
        }

        $this->listeners[$event][$name] = $callback;

        $this->count_listeners++;

        return $name;
    }

    public function removeListener($event, $name) {
        $event = $this->convertEventDescriptor($event);
        $name = $this->convertEventDescriptor($name);

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
        $event = $this->convertEventDescriptor($event);

        return isset($this->listeners[$event]) && (count($this->listeners[$event]) > 0);
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setTick($tick) {
        $this->tick = $tick;
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
