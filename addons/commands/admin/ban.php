<?php

use Dan\Irc\Location\Channel;

command(['ban'])
    ->helpText([
        'Bans the given user with an optional timespan',
        '$ban User 30m',
        '$ban *!*@somerandom.vhost 30m',
        'y = year | M = months | d = days | h = hours | m = minutes',
        'Seconds are not supported',
    ])
    ->rank('hoaq')
    ->handler(function (Channel $channel, $message) {
        $data = explode(' ', $message, 2);

        $channel->ban($data[0], $data[1] ?? null);
    });

command(['unban'])
    ->helpText([
        'Unbans the given user',
    ])
    ->rank('hoaq')
    ->handler(function (Channel $channel, $message) {
        if (!$channel->unban($message)) {
            $channel->message('Ban not found.');
        }
    });
