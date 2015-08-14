<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    message($location, 'http://skycld.co/nbc');
}

if($entry == 'help')
{
    return [
        "NOBODY CARS THAT YOU NEED HELP"
    ];
}