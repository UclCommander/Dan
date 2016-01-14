<?php

use Illuminate\Support\Collection;

hook('roulette')
    ->command(['roulette', 'rr'])
    ->help("yeah, you know what this is.")
    ->func(function(Collection $args) {
        /** @var \Dan\Irc\Location\Channel $channel */
        $channel = $args->get('channel');

        /** @var \Dan\Irc\Location\User $user */
        $user = $args->get('user');

        $message = $args->get('message');

        $round = $channel->data->get('rr.round', 0);
        $bullets = $channel->data->get('rr.bullets', [false, false, false, true, false, false]);

        $shuffle = function() use ($channel, &$bullets) {
            shuffle($bullets);
            $channel->data->put('rr.bullets', $bullets);
            $channel->data->put('rr.round', 0);
            $channel->save();
        };

        if ($message == 'reload') {
            $shuffle();
            return;
        }

        if ($round > 5) {
            $shuffle();
        }

        $response = "The gun clicks";
        $fire = $bullets[$round];

        if ($fire) {
            $response = sprintf("<red>%s dies! D:</red>", $user->nick());
            $shuffle();
        } else {
            $channel->data->put('rr.round', ($round + 1));
        }

        $channel->action(sprintf("<i>points the gun at %s</i>  -  <i>*pulls the trigger*</i>  -  <i>%s</i>", $user->nick(), $response));

        $channel->save();
    });