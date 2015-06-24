<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    $data = explode(' ', $message, 2);

    $kickFrom = $location;

    if(isChannel($data[0]))
    {
        $kickFrom = $data[0];
        array_shift($data);
        $data = explode(' ', $data[1], 2);
    }

    send("KICK", $kickFrom, $data[0], $data[1] ?: "Requested");
}

if($entry == 'help')
{
    return [
        "Kicks a person"
    ];
}