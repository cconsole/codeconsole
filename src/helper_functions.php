<?php

use CodeConsole\MessageClient;

if (!function_exists('cc')) {
    $codeConsoleFrameworkInstance = null;

    function cc()
    {
        global $codeConsoleFrameworkInstance;

        if ($codeConsoleFrameworkInstance === null) {
            $codeConsoleFrameworkInstance = new MessageClient;
        }
        return $codeConsoleFrameworkInstance;
    }
}
