<?php

use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('op')
    ->command(['op'])
    ->console()
    ->rank('aqAS')
    ->help("Ops a user")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        if(!$channel->hasUser($message)) {
            $channel->message("That user is not here!");
            return;
        }

        $channel->userMode(user($message), '+o');
    });


hook('deop')
    ->command(['deop'])
    ->console()
    ->rank('aq')
    ->help("De-Ops a user")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        if(!$channel->hasUser($message)) {
            $channel->message("That user is not here!");
            return;
        }

        $channel->userMode(user($message), '-o');
    });