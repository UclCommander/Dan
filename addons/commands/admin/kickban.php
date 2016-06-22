<?php

use Dan\Irc\Location\Channel;

command(['kickban', 'kb'])
    ->helpText([
        'Bans the given user with an optional timespan, then kicks them.',
        '$ban User 30m',
        '$ban *!*@somerandom.vhost 30m',
        'y = year | M = months | d = days | h = hours | m = minutes',
        'Seconds are not supported',
    ])
    ->rank('hoaq')
    ->handler(function (Channel $channel, $message) {
        $data = explode(' ', $message, 2);

        $channel->ban($data[0], $data[1] ?? null);
        $channel->kick($data[0]);
    });
