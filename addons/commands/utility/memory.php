<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['memory'])
    ->allowPrivate()
    ->allowConsole()
    ->helpText("Gets the bot's memory usage")
    ->rank('ASC')
    ->handler(function (UserContract $user, Channel $channel = null) {
        $memory = convert(memory_get_usage());
        $peak = convert(memory_get_peak_usage());

        $location = $channel ?? $user;

        $location->message("[ <cyan>Memory Usage:</cyan> <yellow>{$memory}</yellow> | <cyan>Peak Usage:</cyan> <yellow>{$peak}</yellow> ]");
    });
