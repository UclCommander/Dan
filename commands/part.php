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
        $reason = isset($partFrom[1]) ? $partFrom[1] : null;
    }

    if(!connection()->inChannel($chan))
    {
        notice($user, "I'm not in this channel!");
        return;
    }

    connection()->partChannel($chan, $reason);
}

if ($entry == 'help')
{
    return [
        "{cp}part - Parts the current channel",
        "{cp}part [channel] - Parts the given channel"
    ];
}
