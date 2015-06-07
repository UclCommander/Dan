<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($channel, "I'm going to need more popcorn! http://skycld.co/morepopcorn");
}

if($entry == 'help')
{
    return [
        "Gives more popcorn."
    ];
}
