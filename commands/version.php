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
    if(!PHAR)
    {
        $v = Dan::getCurrentGitVersion();
       $version = "{yellow}{$v['id']}{reset} | {cyan}{$v['message']}";
    }
    else
        $version = "{cyan} PHAR " . Dan::VERSION;
    
    message($location, "{reset}[ {$version} {reset}]");
}

if($entry == 'help')
{
    return [
        "Gets the current version of Dan."
    ];
}