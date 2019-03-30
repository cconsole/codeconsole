<?php namespace CodeConsole\Services\Requests\Drivers;

use CodeConsole\Services\Requests\RequestDriverInterface;

class LinuxWget implements RequestDriverInterface
{
    private $apiUrl;

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function post($data, $path)
    {
        $content = escapeshellarg(json_encode($data));
        $url = escapeshellarg($this->apiUrl . $path);
        $header = '--header="Content-Type: application/json"';
        $cmd = "wget {$url} --post-data={$content} {$header} > /dev/null 2>/dev/null &";
        shell_exec($cmd);
    }
}