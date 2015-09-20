<?php

/**
 * Gets the current version of dan.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('memory')
    ->command(['memory',])
    ->help('Gets the current memory usage')
    ->func(function(Collection $args) {

        $memory = convert(memory_get_usage());
        $peak   = convert(memory_get_peak_usage());
        $args->get('channel')->message("[ <cyan>Memory Usage:</cyan> <yellow>{$memory}</yellow> | <cyan>Peak Usage:</cyan> <yellow>{$peak}</yellow> ]");
    });