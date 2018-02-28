<?php namespace cconsole;

use GuzzleHttp\Client;
use Psr\Log\LogLevel;

class CodeConsole
{
    private static $client = null;
    private static $apiKey;

    static public function setApiKey($key) 
    {
        self::$apiKey = $key;
    }

    static public function emergency($message, array $context = array())
    {
        self::send(LogLevel::EMERGENCY, $message, $context);
    }

    static public function alert($message, array $context = array())
    {
        self::send(LogLevel::ALERT, $message, $context);
    }

    static public function critical($message, array $context = array())
    {
        self::send(LogLevel::CRITICAL, $message, $context);
    }

    static public function error($message, array $context = array())
    {
        self::send(LogLevel::ERROR, $message, $context);
    }

    static public function warning($message, array $context = array())
    {
        self::send(LogLevel::WARNING, $message, $context);
    }

    static public function notice($message, array $context = array())
    {
        self::send(LogLevel::NOTICE, $message, $context);
    }

    static public function info($message, array $context = array())
    {
        self::send(LogLevel::INFO, $message, $context);
    }

    static public function debug($message, array $context = array())
    {
        self::send(LogLevel::DEBUG, $message, $context);
    }

    static public function log($level, $message, array $context = array())
    {
        self::send($level, $message, $context);
    }

    static private function send($level, $message, $context)
    {
        if (empty(self::$apiKey)) {
            throw new \Exception('Missing API Key');
        }

        if (self::$client === null)
        {
            self::$client = new Client([
                'base_uri' => 'https://api.codeconsole.io',
                'timeout' => 2,
            ]);
        }

        self::$client->post('api/log', [
            'form_params' => [
                'key' => self::$apiKey,
                'type' => $level,
                'data' => json_encode(array_merge(array($message), $context)),
            ]
        ]);
    }
}
