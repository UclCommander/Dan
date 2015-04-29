<?php

namespace {

    use Dan\Console\Console;
    use Dan\Core\Config;
    use Dan\Core\Dan;

    /**
     * Gets the filesystem class.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    function filesystem()
    {
        return Dan::filesystem();
    }

    /**
     * Gets a config item.
     *
     * @param $name
     * @return \Dan\Core\Config|mixed
     */
    function config($name)
    {
        return Config::fetchByKey($name);
    }

    /**
     * Sends a DEBUG message to console.
     *
     * @param $text
     */
    function debug($text)
    {
        Console::debug($text);
    }

    /**
     * Sends an INFO message to console.
     *
     * @param $text
     */
    function info($text)
    {
        Console::info($text);
    }

    /**
     * Sends an ALERT message to console.
     *
     * @param $text
     */
    function alert($text)
    {
        Console::alert($text);
    }

    /**
     * Sends a CRITICAL message to console.
     *
     * @param $text
     * @param bool $die
     */
    function critical($text, $die = false)
    {
        Console::critical($text, $die);
    }


    function message($location, $message)
    {

    }

    function notice($location, $message)
    {

    }
}