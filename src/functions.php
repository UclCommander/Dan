<?php

namespace {

    use Dan\Console\Console;
    use Dan\Core\Config;
    use Dan\Core\Dan;

    /**
     * @return \Illuminate\Filesystem\Filesystem
     */
    function filesystem()
    {
        return Dan::filesystem();
    }

    /**
     * @param $name
     * @return \Dan\Core\Config|mixed
     */
    function config($name)
    {
        return Config::fetchByKey($name);
    }

    /**
     * @param $text
     */
    function debug($text)
    {
        Console::debug($text);
    }

    /**
     * @param $text
     */
    function info($text)
    {
        Console::info($text);
    }
}