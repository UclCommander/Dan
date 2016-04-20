<?php

use Dan\Commands\Command;
use Dan\Core\Dan;
use Dan\Events\Event;
use Dan\Services\ShortLinks\Links;
use Dan\Support\DotCollection;
use Dan\Web\Route;

if (!function_exists('dan')) {

    /**
     * Main Dan Container fetcher.
     *
     * @param null  $make
     * @param array $parameters
     *
     * @return \Dan\Core\Dan|object
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
     * @param string $text
     *
     * @return \Dan\Console\Console
     */
    function console($text = null)
    {
        if (!is_null($text)) {
            console()->message($text);

            return null;
        }

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
     */
    function configPath($path = '')
    {
        return dan()->make('path.config').($path ? '/'.$path : null);
    }
}

if (!function_exists('addonsPath')) {

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
    function events() : \Dan\Events\Handler
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

if (!function_exists('route')) {

    /**
     * Creates a new route.
     *
     * @param $name
     *
     * @return \Dan\Web\Route
     */
    function route($name) : Route
    {
        return dan('web')->registerRoute($name);
    }
}

if (!function_exists('on')) {

    /**
     * Creates a new route.
     *
     * @param $event
     *
     * @return \Dan\Events\Event
     */
    function on($event) : Event
    {
        return dan('events')->registerAddonEvent($event);
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

if (!function_exists('array_random')) {

    /**
     * Because array_rand doesn't do what's expected of it.
     *
     * @param $array
     *
     * @return mixed
     */
    function array_random($array)
    {
        return $array[array_rand($array)];
    }
}

if (!function_exists('shortLink')) {

    /**
     * Because array_rand doesn't do what's expected of it.
     *
     * @param $link
     *
     * @return string
     */
    function shortLink($link)
    {
        if (!config('dan.use_short_links', true)) {
            return $link;
        }

        $class = config('dan.short_link_api', Links::class);

        /** @var \Dan\Contracts\ShortLinkContract $creator */
        $creator = new $class();

        return $creator->create($link);
    }
}

if (!function_exists('cleanString')) {

    /**
     * Removes double spaces and line breaks from a string.
     *
     * @param $string
     *
     * @return string
     */
    function cleanString($string)
    {
        return str_replace(["\n", "\r", '  '], ' ', trim($string));
    }
}

if (!function_exists('formatLocation')) {

    /**
     * @param $string
     *
     * @return array|\Dan\Irc\Location\Channel[]|\Dan\Irc\Connection[]
     */
    function formatLocation($string) : array
    {
        $data = explode(':', $string);

        if (!connection()->hasConnection($data[0])) {
            return [];
        }

        /** @var \Dan\Irc\Connection $connection */
        $connection = connection($data[0]);

        if (!$connection->inChannel($data[1])) {
            return [];
        }

        $channel = $connection->getChannel($data[1]);

        return [
            'connection' => $connection,
            'channel'    => $channel,
        ];
    }
}

if (!function_exists('controlLog')) {

    /**
     * @param $string
     */
    function controlLog($string)
    {
        $data = formatLocation(config('dan.network_console'));

        if (empty($data)) {
            return;
        }

        $data['channel']->message($string);
    }
}

if (!function_exists('relativePath')) {

    /**
     * Gets relative path from executable.
     *
     * @param $path
     *
     * @return string
     */
    function relativePath($path)
    {
        return str_replace(ROOT_DIR, '', $path);
    }
}

if (!function_exists('parseUserString')) {

    /**
     * Parses a user string.
     *
     * @param $string
     *
     * @return string
     */
    function parseUserString($string)
    {
        return preg_split('/(!|@)/', $string);
    }
}

if (!function_exists('noInteractionSetup')) {

    /**
     * Checks for no interaction setup arg.
     *
     * @return string
     */
    function noInteractionSetup()
    {
        global $argv;

        return in_array('--no-interaction-setup', $argv);
    }
}

if (!function_exists('logger')) {

    /**
     * @return \Dan\Log\Logger
     */
    function logger()
    {
        return dan('logger');
    }
}

if (!function_exists('makeMask')) {

    /**
     * Makes a host mask from the given partial mask.
     *
     * @param $partial
     *
     * @return string
     */
    function makeMask($partial)
    {
        $mask = ['*', '*', '*'];

        $data = explode('!', $partial);

        if (count($data) > 1 || (strpos($partial, '!') === false && strpos($partial, '@') === false)) {
            $mask[0] = $data[0];
            array_shift($data);
        }

        $data = explode('@', $data[0]);

        $mask[1] = $data[0] ?? '*';
        $mask[2] = $data[1] ?? '*';

        return "{$mask[0]}!{$mask[1]}@{$mask[2]}";
    }
}

if (!function_exists('intervalTimeToCarbon')) {

    /**
     * @param $time
     *
     * @return \Carbon\Carbon
     */
    function intervalTimeToCarbon($time)
    {
        $map = [
            'y' => 'years',
            'M' => 'months',
            'w' => 'weeks',
            'd' => 'dayz',
            'h' => 'hours',
            'm' => 'minutes',
            's' => 'seconds',
        ];

        $info = str_split(str_replace(' ', '', $time));
        $carbon = new \Carbon\CarbonInterval(null);
        $time = null;

        for ($i = 0; $i < count($info); $i++) {
            if (is_numeric($info[$i])) {
                $time = $info[$i];
                continue;
            }

            if ($time == null) {
                continue;
            }

            $carbon = $carbon->{$map[$info[$i]]}($time);
        }

        return new \Carbon\Carbon($carbon);
    }
}
