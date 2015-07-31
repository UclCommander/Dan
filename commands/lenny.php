<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    switch(trim($message))
    {
        case "hugs":
            $lenny = "(つ ͡° ͜ʖ ͡°)つ";
            break;

        case "no":
            $lenny = "( ͡°_ʖ ͡°)";
            break;

        case "lenninati":
            $lenny = "( ͡∆ ͜ʖ ͡∆)";
            break;

        case "backward":
        case "backwards":
            $lenny = "( °͡ ʖ͜ °͡  )";
            break;

        default:
            $lenny = "( ͡° ͜ʖ ͡°)";
            break;
    }

    message($channel, $lenny);
}

if($entry == 'help')
{
    return [
        "{cp}lenny [type] - The lenny faces",
        'Optional types: hugs, no, lenninati, backwards',
    ];
}