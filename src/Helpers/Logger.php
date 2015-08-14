<?php namespace Dan\Helpers;

class Logger {

    /**
     * Logs to a file.
     *
     * @param $file
     * @param $text
     */
    public static function log($file, $text)
    {
        $date = date('Ymd');

        $path = LOGS_DIR . "/{$date}/";

        if(!filesystem()->exists($path))
            filesystem()->makeDirectory($path, 0755, true);

        $timestamp = "[" . date('m-d-Y H:m:s') . "] ";

        filesystem()->append($path.$file.'.log', $timestamp.ColorParser::strip($text).PHP_EOL);
    }

    /**
     * Logs to chat log file.
     *
     * @param $line
     */
    public static function logChat($line)
    {
        static::log("chatlog", $line);
    }

    /**
     * Logs to debug file.
     *
     * @param $line
     */
    public static function logDebug($line)
    {
        static::log("debug", $line);
    }

    /**
     * Defines session.
     */
    public static function defineSession()
    {
        static::log('chatlog', "-------------------------");
        static::log('chatlog', "------SESSION START------");
        static::log('chatlog', "-------------------------");
        static::log('debug', "-------------------------");
        static::log('debug', "------SESSION START------");
        static::log('debug', "-------------------------");
    }
}