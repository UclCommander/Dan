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
    if(!function_exists('pcntl_exec'))
    {
        message($location, "Unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.");
        return;
    }

    Dan::quit("Restarting bot.");
    pcntl_exec(ROOT_DIR . '/dan', ["--channel={$location}", '--from=update']);
    return;
}

if($entry == 'help')
{
    return [
        "Restarts the bot"
    ];
}