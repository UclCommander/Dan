<?php

use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $partFrom   = explode(' ', $message);
    $chan       = $channel->getLocation();
    $reason     = $message;

    if(isChannel($partFrom[0]))
    {
        $chan   = $partFrom[0];
        $reason = $partFrom[1];
    }

    if(!Dan::connection()->inChannel($chan))
    {
        notice($user, "I'm not in this channel!");
        return;
    }

    Dan::connection()->partChannel($chan, $reason);
}

if ($entry == 'help')
{
    return [
        "part - Parts the current channel",
        "part [channel] - Parts the given channel"
    ];
}
