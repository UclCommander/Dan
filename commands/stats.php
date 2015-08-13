<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $channel = $location->getLocation();

    if($message && isChannel($message))
        $channel = $message;

    $data = database()->table('channels')->where('name', $channel)->first();

    message($location, "{reset}[{cyan} Messages sent: {yellow}{$data['messages']} {reset}|{cyan} Max Users: {yellow}{$data['max_users']} {reset}]");
}

if($entry == 'help')
{
    return [
        "Gets stats for the current or given channel"
    ];
}