<?php

use Dan\Commands\Command;
use Dan\Console\Console;
use Dan\Core\Dan;
use Dan\Support\DotCollection;

if (!function_exists('dan')) {

    /**
     * Main Dan Container fetcher.
     *
     * @param null  $make
     * @param array $parameters
     *
     * @return Dan|mixed
     */
    function dan($make = null, $parameters = [])
    {
        if (is_null($make)) {
            return Dan::getInstance();
        }

        return Dan::getInstance()->make($make, $parameters);
    }
}

if (!function_exists('connection')) {

    /**
     * Gets an active connection.
     *
     * @param null $name
     *
     * @return \Dan\Connection\Handler|\Dan\Contracts\ConnectionContract
     */
    function connection($name = null)
    {
        if (is_null($name)) {
            return dan('connections');
        }

        return dan('connections')->connections($name);
    }
}

if (!function_exists('console')) {

    /**
     * Gets the console object.
     *
     * @return Console
     */
    function console()
    {
        return Dan::getInstance()->make('console');
    }
}

if (!function_exists('config')) {

    /**
     * Gets the console object.
     *
     * @param string $key
     * @param string $default
     *
     * @return \Dan\Config\Config|mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return dan('config');
        }

        return dan('config')->get($key, $default);
    }
}

if (!function_exists('rootPath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function rootPath($path = '')
    {
        return dan()->make('path.root').($path ? '/'.$path : null);
    }
}

if (!function_exists('basePath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function basePath($path = '')
    {
        return dan()->make('path.base').($path ? '/'.$path : null);
    }
}

if (!function_exists('configPath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function configPath($path = '')
    {
        return dan()->make('path.config').($path ? '/'.$path : null);
    }
}

if (!function_exists('hooksPath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function addonsPath($path = '')
    {
        return dan()->make('path.addons').($path ? '/'.$path : null);
    }
}

if (!function_exists('srcPath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function srcPath($path = '')
    {
        return dan()->make('path.src').($path ? '/'.$path : null);
    }
}

if (!function_exists('databasePath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function databasePath($path = '')
    {
        return dan()->make('path.database').($path ? '/'.$path : null);
    }
}

if (!function_exists('storagePath')) {

    /**
     * Gets the config path.
     *
     * @param string $path
     *
     * @return mixed
     */
    function storagePath($path = '')
    {
        return dan()->make('path.storage').($path ? '/'.$path : null);
    }
}

if (!function_exists('dotcollect')) {

    /**
     * Gets the config path.
     *
     * @param array $items
     *
     * @return mixed
     */
    function dotcollect($items = [])
    {
        return new DotCollection($items);
    }
}

if (!function_exists('events')) {

    /**
     * Gets the config path.
     *
     * @return \Dan\Events\Handler
     */
    function events()
    {
        return dan('events');
    }
}

if (!function_exists('filesystem')) {

    /**
     * Gets the config path.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    function filesystem()
    {
        return dan('filesystem');
    }
}

if (!function_exists('database')) {

    /**
     * Grabs the given database or returns the manager if none is given.
     *
     * @param string $name
     *
     * @return \Dan\Database\Database|\Dan\Database\DatabaseManager
     */
    function database($name = null)
    {
        /** @var \Dan\Database\DatabaseManager $database */
        $database = dan('database');

        if (!is_null($name)) {
            return $database->get($name);
        }

        return $database;
    }
}

if (!function_exists('convert')) {

    /**
     * Coverts a number to a human readable amount.
     *
     * @param $size
     *
     * @return string
     */
    function convert($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
    }
}

if (!function_exists('command')) {

    /**
     * @param $names
     *
     * @return \Dan\Commands\Command
     */
    function command($names) : Command
    {
        return dan('commands')->registerCommand((array) $names);
    }
}

if (!function_exists('xmlToArray')) {

    /**
     * Coverts a SimpleXmlElement to an array.
     *
     * @param $data
     *
     * @return mixed
     */
    function xmlToArray($data)
    {
        return json_decode(json_encode($data), true);
    }
}