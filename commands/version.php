<?php

use Dan\Core\Dan;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    message($location, Dan::VERSION);
}

if($entry == 'help')
{
    return [
        "Gets the current version of dan."
    ];
}