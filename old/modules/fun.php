<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

module('fun')
    ->command('slap', ['slaps a user'], new class() extends Command {

        public function irc(User $user, Location $location, string $message)
        {
            $data = explode(' ', $message, 2);

            if($data[0] == connection()->user()->nick())
            {
                message($location, "Hey! That's rude!");
                action($location, "smacks {$user->nick()} on the back of the head");
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

            action($location, "{$verb} {$data[0]} {$after}");
        }
    })
    ->command('lenny', ['gives the booty'], new class() extends Command {

    });