<?php namespace CodeConsole\frameworks;

interface FrameworkInterface
{
    // Check for production environment
    public function isProduction();
}