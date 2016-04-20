<?php

namespace Dan\Log;

use Carbon\Carbon;

class Logger
{
    protected $name;

    protected $sessions = [];

    const LOG = 0;

    const DEBUG = 1;

    const NOTICE = 2;

    const INFO = 3;

    const ERROR = 4;

    const WARNING = 5;

    const CRITICAL = 6;

    protected $level = [
        self::LOG      => 'LOG',
        self::DEBUG    => 'DEBUG',
        self::NOTICE   => 'NOTICE',
        self::INFO     => 'INFO',
        self::ERROR    => 'ERROR',
        self::WARNING  => 'WARNING',
        self::CRITICAL => 'CRITICAL',
    ];

    /**
     * Logger constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $sessions
     */
    public function beginSession($sessions = [])
    {
        foreach ((array) $sessions as $session) {
            if (in_array($session, $this->sessions)) {
                continue;
            }

            $this->write('-------------------------------', $session);
            $this->write('-------- SESSION START --------', $session);
            $this->write('-------------------------------', $session);

            $this->sessions[] = $session;
        }
    }

    /**
     * @param string $network
     * @param string $channel
     * @param string $message
     * @param string|null $user
     */
    public function logNetworkChannelItem($network, $channel, $message, $user = null)
    {
        $file = "{$network}/{$channel}";

        if (!in_array($file, $this->sessions)) {
            $this->beginSession($file);
        }

        $this->addLine(null, ($user ? "{$user}: " : '').$message, $file);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->addLine(static::LOG, $message);
    }

    /**
     * @param $message
     */
    public function debug($message)
    {
        $this->addLine(static::DEBUG, $message, 'debug');
    }

    /**
     * @param \Throwable $throwable
     */
    public function exception(\Throwable $throwable)
    {
        $this->addLine(static::DEBUG, '---------------------------------', 'error');
        $this->addLine(static::DEBUG, 'Exception was thrown.', 'error');
        $this->addLine(static::DEBUG, "Message: {$throwable->getMessage()}", 'error');
        $this->addLine(static::DEBUG, "File: {$throwable->getFile()}", 'error');
        $this->addLine(static::DEBUG, "Line: {$throwable->getLine()}", 'error');
        $this->addLine(static::DEBUG, $throwable->getTraceAsString(), 'error');
        $this->addLine(static::DEBUG, '---------------------------------', 'error');
    }

    /**
     * @param $message
     */
    public function notice($message)
    {
        $this->addLine(static::NOTICE, $message);
    }

    /**
     * @param $message
     */
    public function info($message)
    {
        $this->addLine(static::INFO, $message);
    }

    /**
     * @param $message
     */
    public function warning($message)
    {
        $this->addLine(static::WARNING, $message);
    }

    /**
     * @param $message
     */
    public function error($message)
    {
        $this->addLine(static::ERROR, $message);
    }

    /**
     * @param $message
     */
    public function critical($message)
    {
        $this->addLine(static::CRITICAL, $message);
    }

    /**
     * @param $level
     * @param $message
     * @param null $file
     */
    public function addLine($level, $message, $file = null)
    {
        if (!is_null($level)) {
            $level = "[{$this->level[$level]}] ";
        }

        $this->write($level.$message, $file);
    }

    /**
     * @param $line
     * @param null $file
     */
    protected function write($line, $file = null)
    {
        if ($file == null) {
            $file = $this->name;
        }

        $date = new Carbon();

        if (!file_exists(storagePath("logs/{$file}"))) {
            mkdir(storagePath("logs/{$file}"), 0777, true);
        }

        $line = strip_tags($line);

        file_put_contents(storagePath("logs/{$file}/{$date->toDateString()}.log"), "[{$date->toTimeString()}] {$line}".PHP_EOL, FILE_APPEND);
    }
}
