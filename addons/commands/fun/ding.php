<?php

/**
 * Ding dong!
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['ding'])
    ->allowPrivate()
    ->helpText('Ding dong!')
    ->handler(function (User $user, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('Dong!');
    });
