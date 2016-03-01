<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['quit'])
    ->allowConsole()
    ->allowPrivate()
    ->rank('S')
    ->helpText('Quits the bot.')
    ->handler(function (UserContract $user, Channel $channel = null) {
        $location = $channel ?? $user;
        $location->message("Bye!");
        connection()->disconnectFromAll(true);
    });
