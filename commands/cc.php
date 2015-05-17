<?php

use Dan\Helpers\Web;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $json = Web::json('http://www.classicube.net/api/servers');

    $serverCount    = count($json['servers']);
    $slots          = 0;
    $players        = 0;
    $popular        = '';
    $servPlayer     = 0;
    $servSlots      = 0;
    $active         = 0;

    foreach($json['servers'] as $server)
    {
        $slots      += $server['maxplayers'];
        $players    += $server['players'];

        if($server['players'] > 0)
            $active++;

        if($server['players'] > $servPlayer)
        {
            $popular    = $server['name'];
            $servPlayer = $server['players'];
            $servSlots  = $server['maxplayers'];
        }
    }

    $pop = ($popular != '' ? "'{cyan}{$popular}{reset}' is the most popular with {cyan}{$servPlayer} players{reset} out of {cyan}{$servSlots} slots{reset}." : '');

    $channel->message("{reset}There are {cyan}{$active} out of {$serverCount} servers{reset} active. {$players} players are playing out of {$slots} available slots. {$pop}");
}

if($entry == 'help')
{
    return [
        "Checks ClassiCube server popularity"
    ];
}