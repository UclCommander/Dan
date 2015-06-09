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

    $chan = $channel;

    if(connection()->inChannel($data[0]))
    {
        $chan   = $data[0];
        $message = $data[1];
    }

    if(strpos($message, "!") === 0)
        $message = " {$message}";

    message($chan, $message);
}

if($entry == 'help')
{
    return [
        "{cp}say <message> - Sends <message> to the current channel",
        "{cp}say <channel> <message> - Sends <message> to <channel>"
    ];
}
