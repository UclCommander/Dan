<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    if($message && $user->hasOneOf('hoaq'))
    {
        $data = explode(' ', $message);

        /*if(isset($data[1]) && $data[1] == '-b')
        {
            send('MODE', $channel, "+b", "@{$ban->host()}");
        }
*/
        send('KICK', $channel, $data[0], "http://skycld.co/whoopass");
        return;
    }

    message($channel, "http://skycld.co/whoopass");
}

if($entry == 'help')
{
    return ["When a normal beating just won't do!"];
}