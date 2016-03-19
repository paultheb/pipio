<?php

namespace Pipio\Producer;

class Logger implements \Pipio\Producer {

    const DEFAULT_LOG_LEVEL = 'info';

    const LOG_LEVEL = 'log_level';

    protected $logger;

    protected static $log_levels = [
        'info',
        'debug',
        'notice',
        'emergency',
        'warning',
        'critical',
        'error',
        'alert'
    ];

    public function __construct(\Psr\Log\LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function emit($event, $message) {
        if(
            is_array($message) &&
            isset($message[self::LOG_LEVEL]) &&
            in_array($message[self::LOG_LEVEL], self::$log_levels)
        ) {
            $log_level = $message[self::LOG_LEVEL];
        } else {
            $log_level = self::DEFAULT_LOG_LEVEL;
        }

        $message = is_array($message) ? $message : [$message];

        $this->logger->log($log_level, $event, $message);
    }

}
