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
        $content = escapeshellarg(http_build_query($data));
        $url = escapeshellarg($this->apiUrl . $path);
        $cmd = "wget {$url} --post-data={$content} > /dev/null 2>/dev/null &";
        shell_exec($cmd);
    }
}