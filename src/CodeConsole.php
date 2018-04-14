<?php namespace CodeConsole;

use CodeConsole\Frameworks\CodeIgniter\CodeConsoleCodeIgniter;
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
        $l = 10;
        $r = array('file' => '', 'line' => '');
        $b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $l);

        var_dump($b);

        for($i = 0; $i < $l; $i++) {
            echo "<p>class is {$b[$i]['class']}";
            if (isset($b[$i]['class']) && strpos($b[$i]['class'], 'CodeConsole') === false) {
                break;
            }
        }

        $i--;

        if (isset($b[$i])) {
            $r['file'] = isset($b[$i]['file']) ? $b[$i]['file'] : '';
            $r['line'] = isset($b[$i]['line']) ? $b[$i]['line'] : '';
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

        if (strlen($data) > 10000) {
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