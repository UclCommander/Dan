<?php

/**
 * Ding dong!
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('ding')
    ->command(['ding'])
    ->help('Ding dong!')
    ->func(function(Collection $args) {
        $args->get('channel')->message("Dong!");
    });
