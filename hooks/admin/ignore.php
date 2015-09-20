<?php

/**
 * Ignore command. Ignores a given user or pattern.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Config;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('ignore')
    ->command(['ignore'])
    ->rank('AS')
    ->help('Ignores the given user or pattern')
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        $data = explode(' ', $message);

        $ignore = $data[0];

        if(isUser($ignore) && strpos($ignore, '@') === false)
            $ignore = "*@" . database()->table('users')->where('nick', $ignore)->first()->get('host');

        if(in_array($ignore, config('ignore.masks')))
        {
            Config::remove('ignore.masks', $ignore);
            $channel->message("{$ignore} removed");
        }
        else
        {
            Config::add('ignore.masks', $ignore);
            $channel->message("{$ignore} added");
        }

        Config::saveAll();
    });