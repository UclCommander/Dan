<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $msg = explode(' ', $message, 2);

    if(count($msg) != 2)
        return;

    if($msg[0] == 'bcrypt')
    {
        message($channel, password_hash($msg[1], PASSWORD_BCRYPT));
        return;
    }

    if(in_array($msg[0], hash_algos()))
        message($channel, hash($msg[0], $msg[1]));
}

if($entry == 'help')
{
    return [
        "hash <algo> <text> - Hashes <text> with <algo>",
        "See http://skycld.co/php-hash and http://skycld.co/php-algos for more information.",
        "bcrypt is also available as a type."
    ];
}