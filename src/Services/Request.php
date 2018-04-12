<?php namespace CodeConsole\Services;

class Request
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = defined('CODE_CONSOLE_API_URL') ? CODE_CONSOLE_API_URL : 'https://api.codeconsole.io';
    }

    public function post($data, $path = '/api/log')
    {
        $content = http_build_query($data);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $content,
            )
        );

        $context = stream_context_create($options);
        file_get_contents($this->apiUrl . $path, false, $context);
    }
}