<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    raw($message);
}

if($entry == 'help')
{
    return [
        "Sends a raw IRC line",
    ];
}
