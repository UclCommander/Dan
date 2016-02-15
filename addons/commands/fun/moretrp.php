<?php

/**
 * Gives more popcorn.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['morepopcorn', 'moretrp', 'mp'])
    ->allowPrivate()
    ->helpText('I\'m going to need more popcorn!')
    ->handler(function (User $user, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message('I\'m going to need more popcorn! http://skycld.co/morepopcorn');
    });
