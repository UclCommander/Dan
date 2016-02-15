<?php

/**
 * NBC Command. For those moments when you just don't care.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command('nbc')
    ->allowPrivate()
    ->helpText('NOBODY CARS THAT YOU NEED HELP')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $location->message(($message ? $message.': ' : '') . 'http://skycld.co/nbc');
    });
