<?php

/**
 * Whoopass command. When a normal beating just won't do!
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('whoopass')
    ->command(['whoopass'])
    ->help("When a normal beating just won't do!")
    ->func(function(Collection $args) {
        if($args->get('message') && $args->get('user')->hasOneOf('hoaq'))
        {
            $data = explode(' ', $args->get('message'));

            send('KICK', $args->get('channel'), $data[0], "When a normal beating just won't do! WHOOPASS! Extra strength! http://skycld.co/whoopass");
            return;
        }

        $args->get('channel')->message("When a normal beating just won't do! http://skycld.co/whoopass");
    });