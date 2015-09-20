<?php

/**
 * Roll command. Rolls dice.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('roll')
    ->command(['roll'])
    ->help('roll [sides] - Rolls a dice with [sides] sides. Default is 6')
    ->func(function(Collection $args) {
        $sides = intval($args->get('message'));

        if($sides == 0)
            $sides = 6;

        if($sides == 1)
        {
            $args->get('channel')->message("One side? Isn't that a bit sketchy?");
            return;
        }

        $args->get('channel')->message(rand(1, $sides));
    });
