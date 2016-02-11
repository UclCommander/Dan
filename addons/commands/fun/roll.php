<?php

/**
 * Roll command. Rolls dice.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['roll', 'rtd'])
    ->allowPrivate()
    ->helpText('roll [sides] - Rolls a dice with [sides] sides. Default is 6')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;
        $sides = intval($message);

        if ($sides == 0)
        {
            $sides = 6;
        }

        if ($sides == 1)
        {
            $location->message('One side? Isn\'t that a bit sketchy?');
            return;
        }

        $beginning = '*rolls dice*';

        if (rand(0,5) == 2)
        {
            $beginning = '<bang>*rolling dice intensifies*</bang>';
        }

        $location->message('<i>'.$beginning.'...</i> ' . rand(1, $sides), ['bang' => ['red', null, ['b', 'i']]]);
    });
