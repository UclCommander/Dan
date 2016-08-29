<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;
use Dan\Support\Web;

command(['shorten', 'sl'])
    ->allowPrivate()
    ->allowConsole()
    ->helpText('Shortens given link')
    ->handler(function (UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message(shortLink($message));
    });
