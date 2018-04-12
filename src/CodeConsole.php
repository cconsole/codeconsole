<?php namespace CodeConsole;

use CodeConsole\Frameworks\CodeConsoleCodeIgniter;
use CodeConsole\Services\Request;

abstract class CodeConsole
{
    protected $apiKey;
    protected $apiUrl;
    protected $timers = [];
    protected $framework = null;
    protected $request;

    const LOG = 'log';
    const TIME_START = 'startTimer';
    const TIME_STOP = 'stopTimer';

    public function __construct($apiKey = null)
    {
        if ($apiKey !== null) {
            $this->apiKey = $apiKey;
        } elseif (defined('LARAVEL_START') && function_exists('env') && ($key = env('CODE_CONSOLE_API_KEY')) !== null) {
            $this->apiKey = $key;
        } elseif (defined('CODE_CONSOLE_API_KEY')) {
            $this->apiKey = CODE_CONSOLE_API_KEY;
        } else {
            return;
        }

        $this->determineFramework();
        $this->request = new Request;
    }

    protected function backtrace()
    {
        $r = array('file' => '', 'line' => '');
        $b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        if (isset($b[3])) {
            $r['file'] = $b[3]['file'];
            $r['line'] = $b[3]['line'];
        }
        return $r;
    }

    protected function send($level, $message, $context)
    {
        // API Key has to be set
        if (empty($this->apiKey)) {
            return;
        }

        // Don't run in production
        if ($this->framework !== null && $this->framework->isProduction()) {
            return;
        }

        $data = json_encode(array_merge(array($message), $context));

        if (strlen($data) > 2048) {
            $this->warn('dataTooLarge');
            return;
        }

        $dateUtc = new \DateTime(null, new \DateTimeZone('UTC'));
        $backTrace = $this->backtrace();

        $data = [
            'key' => $this->apiKey,
            'type' => $level,
            'data' => $data,
            't' => $dateUtc->getTimestamp(),
            'b' => json_encode($backTrace),
            'i' => ($level === self::TIME_STOP) ? round($this->timers[$message], 4) : '',
        ];

        $this->request->post($data);
    }

    protected function determineFramework()
    {
        if (defined('CI_VERSION')) {
            $this->framework = new CodeConsoleCodeIgniter();
        }
    }

    protected function warn()
    {
        $this->request->post([
            'type' => 'systemWarning',
            'data' => 'dataTooLarge',
            'key' => $this->apiKey
        ], '/api/warn');
    }
}