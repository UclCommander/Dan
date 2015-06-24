<?php

use Dan\Core\Dan;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Helpers\Web;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    message($channel, "Dan the IRC bot v" . Dan::VERSION . " by UclCommander. http://skycld.co/dan - See " . config('commands.command_prefix') . 'help for a list of commands.');
}

if($entry == 'help')
{
    return [
        "Gives information about the bot."
    ];
}