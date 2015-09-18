<?php

/**
 * Part command. Parts a channel.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('part')
    ->command(['part', 'leave', 'p'])
    ->console()
    ->rank('AS')
    ->help("Leaves a channel")
    ->func(function(Collection $args) {
        $partFrom   = explode(' ', $args->get('message'));
        $chan       = $args->get('channel')->getLocation();
        $reason     = $args->get('message');

        if(isChannel($partFrom[0]))
        {
            $chan   = $partFrom[0];
            $reason = isset($partFrom[1]) ? $partFrom[1] : null;
        }

        if(!connection()->inChannel($chan))
        {
            $args->get('channel')->message("I'm not in this channel!");
            return;
        }

        $args->get('connection')->partChannel($chan, $reason);
    });