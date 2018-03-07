<?php namespace cconsole;

use Psr\Log\LogLevel;

class CodeConsole
{
    private static $client = null;
    private static $apiKey;
    private static $apiUrl;

    const LOG = 'log';

    public static function setApiKey($key) 
    {
        self::$apiKey = $key;
    }

    public static function __callStatic($n, $a)
    {
        if ($n === self::LOG && count($a) === 1) {
            array_unshift($a, LogLevel::NOTICE);
        }

        $c = count($a);

        if ($c === 1) {
            if (is_string($a[0])) {
                return self::{$n}($a[0]);
            }
        } elseif ($c === 2) {
            if ($n === self::LOG) {
                return self::{$a[0]}($a[1]);
            } else {
                if (is_string($a[0]) && is_array($a[1])) {
                    $fn = array_shift($a);
                    return self::{$n}($fn, $a[0]);
                }
            }
        } elseif ($c === 3) {
            if ($n === self::LOG) {
                if (is_string($a[0]) && is_string($a[1]) && is_array($a[2])) {
                    return self::{$a[0]}($a[1], $a[2]);
                }
            }
        }

        if ($n === self::LOG) {
            $fn = array_shift($a);
            $m = array_shift($a);
            return self::{$fn}($m, $a);
        } else {
            $fn = array_shift($a);
            return self::{$n}($fn, $a);
        }
    }

    private static function emergency($message, array $context = array())
    {
        self::send(LogLevel::EMERGENCY, $message, $context);
    }

    private static function alert($message, array $context = array())
    {
        self::send(LogLevel::ALERT, $message, $context);
    }

    private static function critical($message, array $context = array())
    {
        self::send(LogLevel::CRITICAL, $message, $context);
    }

    private static function error($message, array $context = array())
    {
        self::send(LogLevel::ERROR, $message, $context);
    }

    private static function warning($message, array $context = array())
    {
        self::send(LogLevel::WARNING, $message, $context);
    }

    private static function notice($message, array $context = array())
    {
        self::send(LogLevel::NOTICE, $message, $context);
    }

    private static function info($message, array $context = array())
    {
        self::send(LogLevel::INFO, $message, $context);
    }

    private static function debug($message, array $context = array())
    {
        self::send(LogLevel::DEBUG, $message, $context);
    }

    private static function log($level, $message, array $context = array())
    {
        self::send($level, $message, $context);
    }

    private static function backtrace()
    {
        $r = array('file' => '', 'line' => '');
        $b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        if (isset($b[3])) {
            $r['file'] = $b[3]['file'];
            $r['line'] = $b[3]['line'];
        }
        return $r;
    }

    private static function send($level, $message, $context)
    {
        if (empty(self::$apiKey)) {
            if (defined('CODE_CONSOLE_API_KEY')) {
                self::setApiKey(CODE_CONSOLE_API_KEY);
            } else {
                throw new \Exception('Missing API Key');
            }
        }

        $url = defined('CODE_CONSOLE_API_URL') ? CODE_CONSOLE_API_URL : 'https://api.codeconsole.io';
        $dateUtc = new \DateTime(null, new \DateTimeZone('UTC'));
        $backTrace = self::backtrace();

        $content = http_build_query(array(
            'key' => self::$apiKey,
            'type' => $level,
            'data' => json_encode(array_merge(array($message), $context)),
            't' => $dateUtc->getTimestamp(),
            'b' => json_encode($backTrace),
        ));

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $content,
            )
        );

        $context = stream_context_create($options);
        file_get_contents($url . '/api/log', false, $context);
    }
}