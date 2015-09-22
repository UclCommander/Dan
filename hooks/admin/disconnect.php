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
            $network = $args->get('message');

            if(empty($network))
                $network = connection()->getName();

            if($network == 'console')
            {
                $args->get('channel')->message("Connected Networks: " . implode(', ', array_filter(array_keys(config('irc.servers')), function($x) { return Dan::hasConnection($x); })));
                return;
            }

            Dan::self()->disconnect($network);
        }
        catch(Exception $e)
        {
            $args->get('channel')->message($e->getMessage());
        }
    });