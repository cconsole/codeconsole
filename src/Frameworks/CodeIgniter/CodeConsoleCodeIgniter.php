<?php namespace CodeConsole\Frameworks\CodeIgniter;

use CodeConsole\Frameworks\FrameworkInterface;

class CodeConsoleCodeIgniter implements FrameworkInterface
{
    public function isProduction()
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    }
}