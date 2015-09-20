<?php

/**
 * Let me google that for you. For _those_ people.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

hook('lmgtfy')
    ->command(['lmgtfy', 'lazy'])
    ->help('For those lazy people.')
    ->func(function(\Illuminate\Support\Collection $args) {
        $args->get('channel')->message("http://lmgtfy.com/?q=" . urlencode($args->get('message')));
    });