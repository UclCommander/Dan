<?php

/**
 * Kick command. Kicks a user from a channel.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('kick')
    ->command(['kick', 'k'])
    ->console()
    ->rank('oaq')
    ->help([
        'kick <user> [reason] - Kicks <user> with an optional [reason]',
        'kick <channel> <user> [reason] - Kicks <user> from <channel> with an optional [reason]',
    ])
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        $data = explode(' ', $message, 2);

        $kickFrom = $channel;

        if(isChannel($data[0]))
        {
            $kickFrom = $data[0];
            array_shift($data);
            $data = explode(' ', $data[1], 2);
        }

        send("KICK", $kickFrom, $data[0], $data[1] ?: "Requested");
    });