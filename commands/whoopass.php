<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    if($message && $user->hasOneOf('hoaq'))
    {
        send('KICK', $message, "http://skycld.co/whoopass");
        return;
    }

    message($channel, "http://skycld.co/whoopass");
}

if($entry == 'help')
{
    return ["When a normal beating just won't do!"];
}