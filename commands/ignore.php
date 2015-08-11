<?php

use Dan\Core\Config;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = explode(' ', $message);

    $ignore = $data[0];

    if(isUser($ignore))
        $ignore = "*@" . database()->get('users', ['nick' => $ignore])['host'];

    if(in_array($ignore, config('ignore.masks')))
    {
        Config::remove('ignore.masks', $ignore);
        message($channel, "{$ignore} removed");
    }
    else
    {
        Config::add('ignore.masks', $ignore);
        message($channel, "{$ignore} added");
    }

    Config::saveAll();
}

if($entry == 'help')
{
    return [
        "{cp}ignore <mask> - Ignores <mask>."
    ];
}