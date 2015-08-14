<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $sides = intval($message);

    if($sides == 0)
        $sides = 6;

    if($sides == 1)
    {
        message($channel, "One side? Isn't that a bit sketchy?");
        return;
    }

    message($channel, rand(1, $sides));
}

if($entry == 'help')
{
    return [
        "{cp}roll [sides] - Rolls a dice with [sides] sides. Default is 6"
    ];
}