<?php namespace CodeConsole\frameworks;

use CodeConsole\frameworks\FrameworkInterface;

class CodeConsoleCodeIgniter implements FrameworkInterface
{
    public function isProduction()
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    }
}