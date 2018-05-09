<?php namespace CodeConsole\Frameworks;

interface FrameworkInterface
{
    // Check for production environment
    public function isProduction();
}