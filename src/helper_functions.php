<?php

use CodeConsole\LogClient;

if (!function_exists('cc')) {
    $codeConsoleFrameworkInstance = null;

    function cc()
    {
        global $codeConsoleFrameworkInstance;

        if ($codeConsoleFrameworkInstance === null) {
            $codeConsoleFrameworkInstance = new LogClient;
        }
        return $codeConsoleFrameworkInstance;
    }
}
