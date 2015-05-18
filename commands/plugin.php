<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = explode(' ', $message);

    try
    {
        switch ($data[0])
        {
            case 'load':
                if(plugins()->loadPlugin($data[1]))
                    message($channel, "Plugin loaded.");
                break;

            case 'unload':
                plugins()->unloadPlugin($data[1]);
                break;

            case 'loaded':
                message($channel, implode(', ', plugins()->loaded()));
                break;
        }
    }
    catch(Exception $e)
    {
        message($channel, $e->getMessage());
    }
}

if($entry == 'help')
{
    return [
        "join <channel> - Joins <channel> "
    ];
}