<?php namespace CodeConsole\Services\Requests;

use CodeConsole\Services\Requests\Drivers\FileGetContents;
use CodeConsole\Services\Requests\Drivers\LinuxWget;
use CodeConsole\Services\Requests\Drivers\LinuxCurl;

class Log
{
    private $apiUrl;
    private $driver;

    public function __construct()
    {
        $this->apiUrl = defined('CODE_CONSOLE_API_URL') ? CODE_CONSOLE_API_URL : 'https://api.codeconsole.io';
        $this->loadDriver();
    }

    private function loadDriver()
    {
        $disabledFunctions = explode(',', ini_get('disable_functions'));

        if (!in_array('shell_exec', $disabledFunctions)) {
            if (!empty(shell_exec('which curl'))) {
                $this->driver = new LinuxCurl($this->apiUrl);
            } elseif (!empty(shell_exec('which wget'))) {
                $this->driver = new LinuxWget($this->apiUrl);
            }
        }

        if (empty($this->driver)) {
            $this->driver = new FileGetContents($this->apiUrl);
        }
    }

    public function post($data, $path = '/api/log')
    {
        $this->driver->post($data, $path);
    }
}