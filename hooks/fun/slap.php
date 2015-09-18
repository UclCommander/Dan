<?php

/**
 * Slap command. Slaps a user.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('slap')
    ->command(['slap'])
    ->help('Slaps someone')
    ->func(function (Collection $args){
        $message    = $args->get('message');
        $channel    = $args->get('channel');
        $user       = $args->get('user');

        $data = explode(' ', $message, 2);

        if($data[0] == connection()->user->nick())
        {
            $channel->message("Hey! That's rude!");
            $channel->action("smacks {$user->nick()} on the back of the head");
            return;
        }

        $verb = array_random([
            'smacks', 'kicks', 'slaps', 'chops',
            'rekts', 'kills', 'blows up', 'annihilates',
            'roundhouse kicks',
        ]);

        $after = array_random([
            'into a wall', 'into space', 'to death', 'out of the channel',
            'into a pancake', 'into a bacon pancake',
            'into a cupcake'
        ]);

        $channel->action("{$verb} {$data[0]} {$after}");
    });
