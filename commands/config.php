<?php

use Dan\Core\Config;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Illuminate\Support\Arr;


/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

$protectedValues = ['irc.user.pass', 'irc.channels'];

if($entry == 'use' || $entry == 'console')
{
    $data   = explode(' ', $message, 3);
    $key    = isset($data[1]) ? $data[1] : null;
    $value  = isset($data[2]) ? $data[2] : null;

    switch($data[0])
    {
        case 'reload':
            Config::load();
            message($location, 'Config reloaded');
            break;

        case 'set':
            Config::set($key, $value);
            message($location, "Value set.");
            break;

        case 'get':

            if(in_array($key, $protectedValues))
            {
                message($location, 'This value is protected');
                return;
            }

            $arr = [];
            $get = config($key);

            Arr::set($arr, $key, $get);

            if(is_array($get))
            {
                foreach($protectedValues as $hide)
                    if(Arr::has($arr, $hide))
                        Arr::set($arr, $hide, "[Protected value]");

                $get = json_encode(Arr::get($arr, $key));
            }

            message($location, "{$key}: {$get}");

            break;

        case 'add':
            if(is_array(config($key)))
            {
                Config::add($key, $value);
                message($location, "Value added.");
            }
            break;

        case 'remove':

            break;
    }
}

if($entry == 'help')
{
    return [
        "{cp}config reload - Reloads the config",
        "{cp}config get <key> - Gets a config value",
        "{cp}config set <key> <value> - Sets a config value",
        "{cp}config add <key> <value> - Adds an item to an array",
        "{cp}config remove <key> <index> - Removes an item from an array",
    ];
}