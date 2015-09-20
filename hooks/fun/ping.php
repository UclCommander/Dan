<?php

/**
 * Ping pong!.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('ping')
    ->command(['ping'])
    ->console()
    ->help('Ping pong!')
    ->func(function(Collection $args) {
        $args->get('channel')->message("Pong!");
    });
