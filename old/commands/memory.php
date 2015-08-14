<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($location, "[{cyan} Memory Usage: " . convert(memory_get_usage()) . " {reset}|{cyan} Peak Usage: " . convert(memory_get_peak_usage()) . "{reset} ]");
}

if($entry == 'console')
{
    message($location, "Memory Usage: " . convert(memory_get_usage()));
    message($location, "Peak Usage: " . convert(memory_get_peak_usage()));
}

if($entry == 'help')
{
    return [
        "Gets memory usage"
    ];
}