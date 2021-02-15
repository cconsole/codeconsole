<?php

namespace CodeConsole;

use CodeConsole\Frameworks\CodeIgniter\CodeConsoleCodeIgniter;
use CodeConsole\Services\Requests\Log;
use CodeConsole\Frameworks\Laravel\CodeConsoleLaravel;

abstract class CodeConsole
{
    protected $apiKey;
    protected $apiUrl;
    protected $timers = [];
    protected $framework = null;
    protected $request;
    protected $scriptStart;
    protected $lastHash = null;
    protected $callCount = 0;
    protected $processId;

    const LOG = 'log';
    const TIME_START = 'startTimer';
    const TIME_STOP = 'stopTimer';

    public function __construct($apiKey = null)
    {
        $this->scriptStart = (!empty($_SERVER['REQUEST_TIME_FLOAT'])) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true);

        if ($apiKey !== null) {
            $this->apiKey = $apiKey;
        } elseif (defined('LARAVEL_START') && function_exists('env') && ($key = env('CODE_CONSOLE_PROJECT_KEY')) !== null) {
            $this->apiKey = $key;
        } elseif (defined('CODE_CONSOLE_PROJECT_KEY')) {
            $this->apiKey = CODE_CONSOLE_PROJECT_KEY;
        } else {
            return;
        }

        $this->determineFramework();
        $this->request = new Log;
        $this->processId = uniqid();
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

        for ($i = 0; $i < $l; $i++) {
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

    protected function backtraceV2()
    {
        return array_reduce(
            array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)),
            function ($result, $b) {
                if (isset($b['class']) && strpos($b['class'], 'CodeConsole\CodeConsole') === false) {
                    $result[] = [
                        'file' => isset($b['file']) ? $b['file'] : null,
                        'line' => isset($b['line']) ? $b['line'] : null
                    ];
                }
                return $result;
            },
            []
        );
    }

    protected function determineFramework()
    {
        if (defined('CI_VERSION')) {
            $this->framework = new CodeConsoleCodeIgniter();
        } else {
            $composerPath = realpath('composer.json');
            if (is_readable($composerPath)) {
                $composer = json_decode(file_get_contents($composerPath), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($composer['name']) && $composer['name'] === 'laravel/laravel') {
                    $this->framework = new CodeConsoleLaravel();
                }
            }
        }
    }

    protected function send($level, $message, $context)
    {
        $postData = (defined('CODE_CONSOLE_BETA') && CODE_CONSOLE_BETA === true)
            ? [
                'projectKey' => $this->apiKey,
                'processId' => $this->processId,
                'startTime' => $this->scriptStart,
                'time' => (new \DateTime(null, new \DateTimeZone('UTC')))->getTimestamp(),
                'type' => 'log',
                'data' => json_encode([
                    'level' => $level,
                    'log' => array_merge([$message], $context),
                ]),
                'backtrace' => $this->backtraceV2(),
                'count' => ++$this->callCount,
            ]
            : [
                'type' => $level,
                'data' => json_encode(array_merge(array($message), $context)),
                'b' => json_encode($this->backtrace()),
                'i' => ($level === self::TIME_STOP) ? round($this->timers[$message], 4) : '',
            ];

        $this->post($postData, '/log');
    }

    protected function warn()
    {
        $this->request->post([
            'type' => 'systemWarning',
            'data' => 'dataTooLarge',
            'key' => $this->apiKey
        ], '/log');
    }

    protected function recordEnd($wallTime)
    {
        $this->post([
            'type' => 'scriptEnd',
            'data' => $wallTime,
        ], '/log');
    }

    protected function post($data, $path)
    {
        if (empty($this->apiKey)) {
            return;
        }

        if ($this->framework !== null && $this->framework->isProduction()) {
            return;
        }

        if (isset($data['data'])) {
            if (strlen($data['data']) > 10000) {
                if (defined('CODE_CONSOLE_BETA') && CODE_CONSOLE_BETA === true) {
                    $data = array_merge($data, [
                        'data' => json_encode(array_merge((array) json_decode($data['data']), [
                            'log' => ['Data sent exceeds the 10,000 character limit'],
                            'level' => 'warning',
                        ])),
                        'type' => 'tooLarge',
                    ]);
                } else {
                    $this->warn('dataTooLarge');
                    return;
                }
            }
            $data['data'] = json_decode($data['data']);
        }

        $dateUtc = new \DateTime(null, new \DateTimeZone('UTC'));
        $logTime = $dateUtc->getTimestamp();

        $postData = (defined('CODE_CONSOLE_BETA') && CODE_CONSOLE_BETA === true)
            ? ($data['type'] === 'scriptEnd'
                ? [
                    'projectKey' => $this->apiKey,
                    'processId' => $this->processId,
                    'time' => (new \DateTime(null, new \DateTimeZone('UTC')))->getTimestamp(),
                    'count' => ++$this->callCount,
                ]
                : [])
            : [
                'key' => $this->apiKey,
                't' => $logTime,
                'wf' => $this->lastHash,
            ];

        $this->request->post($postData + $data, $path);

        $this->lastHash = md5(preg_replace('/[^\w]/', '', $logTime . json_encode($data['data'])));
    }
}
