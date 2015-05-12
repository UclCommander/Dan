<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($channel, "[{cyan} Memory Usage: " . convert(memory_get_usage()) . " {reset}|{cyan} Peak Usage: " . convert(memory_get_peak_usage()) . "{reset} ]");
}

if($entry == 'help')
{
    return "Gets memory usage";
}