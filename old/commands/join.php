<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    connection()->joinChannel($message);
}

if($entry == 'help')
{
    return [
        "{cp}join <channel> - Joins <channel> "
    ];
}