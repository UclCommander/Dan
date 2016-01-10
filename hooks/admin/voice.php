<?php

use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('voice')
    ->command(['voice'])
    ->console()
    ->rank('hoaqAS')
    ->help("Voices a user")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        if($message == '*') {
            $users = [];

            foreach($channel->getUsers() as $user) {
                $users[] = $user['nick'];

                if(count($users) == 4) {
                    connection()->send('MODE', $channel, "+" . str_repeat('v', count($users)), ...$users);
                    $users = [];
                }
            }

            if(!empty($users)) {
                connection()->send('MODE', $channel, "+".str_repeat('v', count($users)), ...$users);
            }
            return;
        }

        $channel->userMode(user($message), '+v');
    });


hook('devoice')
    ->command(['devoice'])
    ->console()
    ->rank('hoaq')
    ->help("Devoices a user")
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel = $args->get('channel');
        $message = $args->get('message');

        if($message == '*') {
            $users = [];

            foreach($channel->getUsers() as $user) {
                $users[] = $user['nick'];

                if(count($users) == 4) {
                    connection()->send('MODE', $channel, "-" . str_repeat('v', count($users)), ...$users);
                    $users = [];
                }
            }

            if(!empty($users)) {
                connection()->send('MODE', $channel, "-".str_repeat('v', count($users)), ...$users);
            }
            return;
        }

        $channel->userMode(user($message), '-v');
    });