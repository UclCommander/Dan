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

    if(isUser($ignore) && strpos($ignore, '@') === false)
        $ignore = "*@" . database()->table('users')->where('nick', $ignore)->first()->get('host');

    if(in_array($ignore, config('ignore.masks')))
    {
        Config::remove('ignore.masks', $ignore);
        $text = "{$ignore}{cyan} removed";
    }
    else
    {
        Config::add('ignore.masks', $ignore);
        $text = "{$ignore}{cyan} added";
    }

    message($channel, "{reset}[{yellow} {$text} {reset}]");

    Config::saveAll();
}

if($entry == 'help')
{
    return [
        "{cp}ignore <mask> - Ignores <mask>"
    ];
}