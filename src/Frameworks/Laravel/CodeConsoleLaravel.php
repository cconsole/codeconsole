<?php namespace CodeConsole\Frameworks\Laravel;

use CodeConsole\Frameworks\FrameworkInterface;

class CodeConsoleLaravel implements FrameworkInterface
{
    public function isProduction()
    {
        if (function_exists('app') && method_exists(app(), 'isLocal')) {
            return !app()->isLocal();
        } else {
            return getenv('APP_ENV') === 'production';
        }
    }
}