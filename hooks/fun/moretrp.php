<?php

/**
 * Gives more popcorn.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('morepopcorn')
    ->command(['morepopcorn', 'moretrp', 'mp'])
    ->help('I\'m going to need more popcorn!')
    ->func(function(Collection $args) {
        $args->get('channel')->message("I'm going to need more popcorn! http://skycld.co/morepopcorn");
    });
