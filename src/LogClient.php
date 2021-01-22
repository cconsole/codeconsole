<?php

namespace CodeConsole;

use Psr\Log\LogLevel;

class LogClient extends CodeConsole
{
    public function emergency($message, ...$args)
    {
        return $this->send(LogLevel::EMERGENCY, $message, $args);
    }

    public function alert($message, ...$args)
    {
        return $this->send(LogLevel::ALERT, $message, $args);
    }

    public function critical($message, ...$args)
    {
        return $this->send(LogLevel::CRITICAL, $message, $args);
    }

    public function error($message, ...$args)
    {
        return $this->send(LogLevel::ERROR, $message, $args);
    }

    public function warning($message, ...$args)
    {
        return $this->send(LogLevel::WARNING, $message, $args);
    }

    public function notice($message, ...$args)
    {
        return $this->send(LogLevel::NOTICE, $message, $args);
    }

    public function info($message, ...$args)
    {
        return $this->send(LogLevel::INFO, $message, $args);
    }

    public function debug($message, ...$args)
    {
        return $this->send(LogLevel::DEBUG, $message, $args);
    }

    public function log($message, ...$args)
    {
        return $this->send(self::LOG, $message, $args);
    }

    public function startTimer($name = 'default', ...$args)
    {
        $this->timers[$name] = microtime(true);
        $this->send(self::TIME_START, $name, $args);
    }

    public function stopTimer($name = 'default', ...$args)
    {
        if (isset($this->timers[$name])) {
            $this->timers[$name] = microtime(true) - $this->timers[$name];
        }
        $this->send(self::TIME_STOP, $name, $args);
    }
}
