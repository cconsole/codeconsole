<?php namespace cconsole\frameworks;

use cconsole\frameworks\FrameworkInterface;

class CodeConsoleCodeIgniter implements FrameworkInterface
{
    public function isProduction()
    {
        return defined('ENVIRONMENT') && defined('ENVIRONMENT_PRODUCTION') && ENVIRONMENT === ENVIRONMENT_PRODUCTION;
    }
}