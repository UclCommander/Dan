<?php

/**
 * Join command. Joins a channel.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('join')
    ->command(['join', 'j'])
    ->console()
    ->rank('AS')
    ->help("Joins a channel")
    ->func(function(Collection $args) {
        $args->get('connection')->joinChannel($args->get('message'));
    });