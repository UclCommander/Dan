<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($channel, 'popcorn anyone? http://skycld.co/popcorn');
}

if($entry == 'help')
{
    return [
        "Gives popcorn."
    ];
}