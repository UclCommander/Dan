<?php

/**
 * ClassiCube command. Tells you how dead classic is.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Helpers\Web;
use Illuminate\Support\Collection;

hook('classicube')
    ->command(['classicube', 'cc'])
    ->help("Ask the 8ball a question")
    ->func(function (Collection $args){

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

        $pop = ($popular != '' ? "<yellow>{$popular}</yellow> <cyan>is the most popular with</cyan> <yellow>{$servPlayer}</yellow> <cyan>players out of</cyan> <yellow>{$servSlots}</yellow> <cyan>slots</cyan>." : '');

        $areis = $active == 1 ? 'is' : 'are';

        $args->get('channel')->message("<cyan>There {$areis}</cyan> <yellow>{$active}</yellow> <cyan>out of</cyan> <yellow>{$serverCount}</yellow><cyan>servers active.</cyan> <yellow>{$players}</yellow> <cyan>players are playing out of</cyan> <yellow>{$slots}</yellow> <cyan>available slots.</cyan> {$pop}");

    });
