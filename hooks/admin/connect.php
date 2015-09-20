<?php

/**
 * Connect command. Connects to a network.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('connect')
    ->command(['connect'])
    ->console()
    ->rank('S')
    ->help("Connects to a network")
    ->func(function(Collection $args) {
        try
        {
            Dan::self()->connect($args->get('message'));
        }
        catch(Exception $e)
        {
            $args->get('channel')->message($e->getMessage());
        }
    });