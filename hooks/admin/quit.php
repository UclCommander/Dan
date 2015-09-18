<?php

/**
 * Quit command. Gracefully stops the bot and saves the database all configuration.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('quit')
    ->command(['quit'])
    ->console()
    ->rank('S')
    ->help('Makes the bot quit')
    ->func(function(Collection $args) {
        Dan::quit($args['message']);
    });
