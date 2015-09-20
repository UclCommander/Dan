<?php

/**
 * Config command. Set configuration values during runtime.
 *
 * config reload - Reloads configuration from file.
 * config set <key> <value> - Sets <value> on <key>.
 * config get <key> - Gets the value of <key>.
 * config add <key> <value> - Adds <value> to the <key> array.
 * config remove <key> <value> - Removes <value> from the <key> array.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Config;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

hook('config')
    ->command(['config'])
    ->console()
    ->rank('S')
    ->help([
        'config reload - Reloads configuration from file.',
        'config set <key> <value> - Sets <value> on <key>.',
        'config get <key> - Gets the value of <key>.',
        'config add <key> <value> - Adds <value> to the <key> array.',
        'config remove <key> <value> - Removes <value> from the <key> array.',
    ])
    ->func(function(Collection $args) {
        $protectedValues = ['irc.*.user.pass', 'irc.*.control_channel', 'irc.*.channels'];

        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        $data   = explode(' ', $message, 3);
        $key    = isset($data[1]) ? $data[1] : null;
        $value  = isset($data[2]) ? $data[2] : null;

        switch($data[0])
        {
            case 'reload':
                Config::load();
                $channel->message('Config reloaded');
                break;

            case 'set':
                Config::set($key, $value);
                $channel->message("Value set.");
                break;

            case 'get':

                foreach($protectedValues as $protected)
                {
                    if(fnmatch($protected, $key))
                    {
                        $channel->message('This value is protected');
                        return;
                    }
                }

                $arr = [];
                $get = config($key);

                Arr::set($arr, $key, $get);

                if(is_array($get))
                {
                    foreach($protectedValues as $protected)
                        if(fnmatch($protected, $key))
                            Arr::set($arr, $key, "[Protected value]");

                    $get = json_encode(Arr::get($arr, $key));
                }

                $channel->message("{$key}: {$get}");

                break;

            case 'add':
                if(is_array(config($key)))
                {
                    Config::add($key, $value);
                    $channel->message("Value added.");
                }
                break;

            case 'remove':
                if(is_array(config($key)))
                {
                    Config::remove($key, $value);
                    $channel->message("Value removed.");
                }
                break;
        }

        Config::saveAll();
    });