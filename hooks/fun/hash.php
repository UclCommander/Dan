<?php

/**
 * Runs hash() on the given string
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('hash')
    ->command(['hash'])
    ->help([
        "hash <algo> <text> - Hashes <text> with <algo>",
        "See http://skycld.co/php-hash and http://skycld.co/php-algos for more information.",
        "bcrypt is also available as a type."
    ])
    ->func(function (Collection $args){

        $channel = $args->get('channel');
        $message = $args->get('message');

        $msg = explode(' ', $message, 2);

        if (count($msg) != 2) {
            if (in_array($msg[0], array_merge(hash_algos(), ['bcrypt']))) {
                $channel->message("Please specify text to hash.");
                return;
            }
            $channel->message("Please specify a valid algorithm.");
            return;
        }

        if ($msg[0] == 'bcrypt') {
            $channel->message(password_hash($msg[1], PASSWORD_BCRYPT));
            return;
        }

        if (in_array($msg[0], hash_algos())) {
            $channel->message(hash($msg[0], $msg[1]));
            return;
        }

        $channel->message("Please specify a valid algorithm.");
    });
