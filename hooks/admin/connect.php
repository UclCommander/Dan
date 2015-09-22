<?php

/**
 * Connect command. Connects to a network.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('connect')
    ->command(['connect'])
    ->console()
    ->rank('S')
    ->help("Connects to a network")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');

        try
        {
            $network = $args->get('message');

            if(empty($network))
            {
                $channel->message("Connected Networks: " . implode(', ', array_filter(array_keys(config('irc.servers')), function($x) { return Dan::hasConnection($x); })));
                $channel->message("Available Networks: " . implode(', ', array_filter(array_keys(config('irc.servers')), function($x) { return !Dan::hasConnection($x); })));
                return;
            }

            if(!array_key_exists($network, config('irc.servers')))
            {
                $channel->message("This network has no configuration set.");
                return;
            }

            $channel->message("Connecting to the network <i>{$network}</i>");

            if(Dan::self()->connect($network))
            {
                $channel->message("Connected to the network.");
                return;
            }

            $channel->message("Error connecting to network.");
        }
        catch(Exception $e)
        {
            $channel->message($e->getMessage());
        }
    });