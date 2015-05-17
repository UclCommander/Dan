<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($channel, 'http://skycld.co/nbc');
}

if($entry == 'help')
{
    return [
        "NOBODY CARS THAT YOU NEED HELP."
    ];
}