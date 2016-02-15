<?php

/**
 * Gives popcorn.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command('ping')
    ->allowPrivate()
    ->helpText('Gives popcorn.')
    ->handler(function (User $user, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('popcorn anyone? http://skycld.co/popcorn');
    });
