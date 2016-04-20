<?php

use Carbon\Carbon;
use Dan\Irc\Location\Channel;

command(['mute'])
    ->handler(function (Channel $channel, $message) {
        $data = explode(' ', $message, 2);

        if (!$channel->hasUser($data[0])) {
            $channel->message('User is not in the channel.');

            return;
        }

        $channel->mute($data[0], $data[1] ?? null);
    });

command(['unmute'])
    ->handler(function (Channel $channel, $message) {
        if (!$channel->hasUser($message)) {
            $channel->message('User is not in the channel.');

            return;
        }

        $channel->unmute($message);
    });