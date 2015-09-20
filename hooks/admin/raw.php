<?php

/**
 * Raw command. Sends a raw IRC line.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('raw')
    ->command(['raw'])
    ->console()
    ->rank('S')
    ->help("Sends a <i>RAW</i> IRC line.")
    ->func(function(Collection $args) {
        $args->get('connection')->raw($args->get('message'));
    });