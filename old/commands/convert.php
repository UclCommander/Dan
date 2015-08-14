<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Helpers\Web;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $query = urlencode("convert {$message}");
    $request = Web::json("http://api.duckduckgo.com/?q={$query}&format=json&pretty=1");

    if(empty($request) || $request['Answer'] == '')
    {
        message($channel, "[ No Results ]");
        return;
    }

    message($channel, "{reset}[ {yellow}{$message}:{cyan} {$request['Answer']} {reset}]");
}

if($entry == 'help')
{
    return [
        "Converts something to something else using DuckDuckGo"
    ];
}