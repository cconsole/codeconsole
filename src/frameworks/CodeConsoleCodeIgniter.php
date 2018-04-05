<?php namespace cconsole\frameworks;

use cconsole\frameworks\FrameworkInterface;

class CodeConsoleCodeIgniter implements FrameworkInterface
{
    public function isProduction()
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'production';
    }
}