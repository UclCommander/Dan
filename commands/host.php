<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    if(empty($message))
    {
        $host = $user->host();
    }
    else
    {
        $data = database()->table('users')->where('nick', $message)->first();

        if(!$data->count())
        {
            message($location, "I've never seen this user.");
            return;
        }

        $host = $data->get('host');
    }


    message($location, "{reset}[{cyan} Last known host: {$host} {reset}]");
}

if($entry == 'help')
{
    return [
        "Gets a user host."
    ];
}