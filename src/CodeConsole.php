<?php namespace cconsole;

use GuzzleHttp\Client;

class CodeConsole
{
    private static $client = null;
    private static $apiKey;

    const LOG = 'log';
    const ERROR = 'error';
    const INFO = 'info';
    const WARN = 'warn';

    static public function setApiKey($key) 
    {
        self::$apiKey = $key;
    }

    static public function log()
    {
        self::send(self::LOG, func_get_args());
    }

    static public function error()
    {
        self::send(self::ERROR, func_get_args());
    }

    static public function info()
    {
        self::send(self::INFO, func_get_args());
    }

    static public function warn()
    {
        self::send(self::WARN, func_get_args());
    }

    static private function send($level, $context)
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
                'data' => json_encode($context),
            ]
        ]);
    }
}
