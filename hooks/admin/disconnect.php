<?php

/**
 * Disconnect command. Disconnects from a network.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('disconnect')
    ->command(['disconnect'])
    ->console()
    ->rank('S')
    ->help("Disconnect from a network")
    ->func(function(Collection $args) {
        try
        {
            Dan::self()->disconnect($args->get('message'));
        }
        catch(Exception $e)
        {
            $args->get('channel')->message($e->getMessage());
        }
    });