<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = explode(' ', $message, 2);

    if(connection()->inChannel($data[0]))
    {
        message($data[0], $data[1]);
        return;
    }

    message($channel, $message);
}

if($entry == 'help')
{
    return [
        "say <message> - Sends <message> to the current channel",
        "say <channel> <message> - Sends <message> to <channel>"
    ];
}
