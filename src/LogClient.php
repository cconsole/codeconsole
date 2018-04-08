<?php namespace CodeConsole;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class LogClient extends CodeConsole implements LoggerInterface
{
    public function emergency($message, array $context = [])
    {
        return $this->send(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        return $this->send(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        return $this->send(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        return $this->send(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        return $this->send(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        return $this->send(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        return $this->send(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        return $this->send(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        return $this->send($level, $message, $context);
    }

    public function startTimer($name = 'default', array $context = [])
    {
        $this->timers[$name] = microtime(true);
        $this->send(self::TIME_START, $name, $context);
    }

    public function stopTimer($name = 'default', array $context = [])
    {
        if (isset($this->timers[$name])) {
            $this->timers[$name] = microtime(true) - $this->timers[$name];
        }
        $this->send(self::TIME_STOP, $name, $context);
    }
}