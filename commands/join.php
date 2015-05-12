<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    connection()->joinChannel($message);
}

if($entry == 'help')
{
    return [
        "join <channel> - Joins <channel> "
    ];
}