<?php namespace CodeConsole\Services\Requests\Drivers;

use CodeConsole\Services\Requests\RequestDriverInterface;

class FileGetContents implements RequestDriverInterface
{
    private $apiUrl;

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function post($data, $path)
    {
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode($data),
            )
        );

        $context = stream_context_create($options);
        @file_get_contents($this->apiUrl . $path, false, $context);
    }
}