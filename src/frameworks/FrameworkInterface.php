<?php namespace cconsole\frameworks;

interface FrameworkInterface
{
    // Check for production environment
    public function isProduction();
}