<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

on('irc.join')
    ->name('autovoice')
    ->priority(\Dan\Events\Event::Low)
    ->handler(function (Connection $connection, Channel $channel, User $user) {
        $channel->mode('+v', $user);
    });
