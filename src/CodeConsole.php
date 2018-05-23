<?php namespace CodeConsole;

use CodeConsole\Frameworks\CodeIgniter\CodeConsoleCodeIgniter;
use CodeConsole\Services\Requests\Log;

abstract class CodeConsole
{
    protected $apiKey;
    protected $apiUrl;
    protected $timers = [];
    protected $framework = null;
    protected $request;
    protected $scriptStart;
    protected $lastHash = null;

    const LOG = 'log';
    const TIME_START = 'startTimer';
    const TIME_STOP = 'stopTimer';

    public function __construct($apiKey = null)
    {
        $this->scriptStart = (!empty($_SERVER['REQUEST_TIME_FLOAT'])) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);

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
        $this->request = new Log;
    }

    public function __destruct()
    {
        $wallTime = round(microtime(true) - $this->scriptStart, 4);
        $this->recordEnd($wallTime);
    }

    protected function backtrace()
    {
        $l = 10;
        $r = array('file' => '', 'line' => '');
        $b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $l);

        for($i = 0; $i < $l; $i++) {
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

    protected function determineFramework()
    {
        if (defined('CI_VERSION')) {
            $this->framework = new CodeConsoleCodeIgniter();
        }
    }

    protected function send($level, $message, $context)
    {
        $data = json_encode(array_merge(array($message), $context));

        $backTrace = $this->backtrace();

        $postData = [
            'type' => $level,
            'data' => $data,
            'b' => json_encode($backTrace),
            'i' => ($level === self::TIME_STOP) ? round($this->timers[$message], 4) : '',
        ];

        $this->post($postData, '/api/log');
    }

    protected function warn()
    {
        $this->request->post([
            'type' => 'systemWarning',
            'data' => 'dataTooLarge',
            'key' => $this->apiKey
        ], '/api/warn');
    }

    protected function recordEnd($wallTime)
    {
        $this->post([
            'type' => 'scriptEnd',
            'data' => $wallTime,
        ], '/api/record_end');
    }

    protected function post($data, $path)
    {
         // API Key has to be set
        if (empty($this->apiKey)) {
            return;
        }

        // Don't run in production
        if ($this->framework !== null && $this->framework->isProduction()) {
            return;
        }

        if (isset($data['data']) && strlen($data['data']) > 10000) {
            $this->warn('dataTooLarge');
            return;
        }

        $dateUtc = new \DateTime(null, new \DateTimeZone('UTC'));
        $logTime = $dateUtc->getTimestamp();

        $postData = [
            'key' => $this->apiKey,
            't' => $logTime,
            'wf' => $this->lastHash,
        ];

        $this->request->post($postData + $data, $path);

        $this->lastHash = md5($logTime . $data['data']);
    }
}