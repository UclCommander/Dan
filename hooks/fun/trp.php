<?php

/**
 * Gives popcorn.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('popcorn')
    ->command(['popcorn', 'trp'])
    ->help('Gives popcorn.')
    ->func(function(Collection $args) {
        $args->get('channel')->message("popcorn anyone? http://skycld.co/popcorn");
    });
