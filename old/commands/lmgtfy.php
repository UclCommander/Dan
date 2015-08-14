<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($channel, "http://lmgtfy.com/?q=" . urlencode($message));
}

if($entry == 'help')
{
    return [
        "Let me google that for you..."
    ];
}