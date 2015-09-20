<?php

/**
 * Raw command. Sends a raw IRC line.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Setup\Update;
use Illuminate\Support\Collection;

hook('update')
    ->command(['update'])
    ->console()
    ->rank('S')
    ->help("Updates the bot.")
    ->func(function(Collection $args) {
        if($args->get('message') != 'do')
        {
            if(!Update::check())
            {
                $args->get('channel')->message('No updates found.');
                return;
            }

            $args->get('channel')->message('Update found! Run <i>update do</i> to update the bot automatically.');

            return;
        }

        Update::go($args->get('connection'));
    });