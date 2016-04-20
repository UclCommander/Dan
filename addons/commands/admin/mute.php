<?php

use Carbon\Carbon;
use Dan\Irc\Location\Channel;

command(['mute'])
    ->helpText([
        'Mutes the given user with an optional timespan',
        '$mute User 30m',
        'y = year | M = months | d = days | h = hours | m = minutes',
        'Seconds are not supported'
    ])
    ->rank('hoaq')
    ->handler(function (Channel $channel, $message) {
        $data = explode(' ', $message, 2);

        if (!$channel->hasUser($data[0])) {
            $channel->message('User is not in the channel.');

            return;
        }

        $channel->mute($data[0], $data[1] ?? null);
    });

command(['unmute'])
    ->helpText([
        'Unmutes the given user'
    ])
    ->rank('hoaq')
    ->handler(function (Channel $channel, $message) {
        if (!$channel->hasUser($message)) {
            $channel->message('User is not in the channel.');

            return;
        }

        $channel->unmute($message);
    });