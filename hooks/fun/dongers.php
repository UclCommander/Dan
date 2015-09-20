<?php

/**
 * ヽ༼ຈل͜ຈ༽ﾉ
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('dongers')
    ->command(['dongers'])
    ->help('RAISE THE DONGERS')
    ->func(function(Collection $args) {
        $args->get('channel')->message("ヽ༼ຈل͜ຈ༽ﾉ raise your dongers ヽ༼ຈل͜ຈ༽ﾉ'");
    });