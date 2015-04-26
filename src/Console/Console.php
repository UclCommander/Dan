<?php namespace Dan\Console;

use \Exception;

class Console {

    /**
     * Parses PHP CLI args.
     *
     * @param array $args
     * @return array
     */
    public static function parseArgs(array $args)
    {
        $parsed = [];

        foreach($args as $arg)
        {
            $values = explode('=', $arg);

            $parsed[$values[0]] = isset($values[1]) ? $values[1] : true;
        }

        return $parsed;
    }

    /**
     * Sends a generic message.
     *
     * @param string $message
     * @param bool $color
     * @return string
     */
    public static function send($message, $color = true)
    {
        if($color)
            $message = ConsoleFormat::parse($message);

        echo $message . PHP_EOL;
    }

    /**
     * Sends an SUCCESS message.
     *
     * @param $message
     */
    public static function success($message)
    {
        static::send("{green}[SUCCESS] {$message}");
    }

    /**
     * Sends an ALERT message.
     *
     * @param $message
     */
    public static function alert($message)
    {
        static::send("{yellow}[ALERT] {$message}");
    }

    /**
     * Sends an INFO message.
     *
     * @param $message
     */
    public static function info($message)
    {
        static::send("{blue}[INFO] {$message}");
    }

    /**
     * Sends a DEBUG message.
     *
     * @param $message
     */
    public static function debug($message)
    {
        static::send("{purple}[DEBUG]{cyan} {$message}");
    }

    /**
     * Sends a CRITICAL message.
     *
     * @param $message
     */
    public static function critical($message)
    {
        static::send("{red}[CRITICAL] {$message}");
    }

    /**
     * @param \Exception $exception
     */
    public static function exception(Exception $exception)
    {
        static::send("{red}----PHP EXCEPTION----");
        static::send("{red}Message: {cyan}{$exception->getMessage()}");
        static::send("{red}File: {cyan}{$exception->getFile()}");
        static::send("{red}Line: {cyan}{$exception->getLine()}");
        static::send("{red}Trace: {cyan}");
        static::send($exception->getTraceAsString());
        static::send("{red}----END EXCEPTION----");
    }
}