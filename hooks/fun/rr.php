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

        $shuffle = function($reset = false) use ($channel, &$bullets) {
            shuffle($bullets);
            $channel->data->put('rr.bullets', $bullets);

            if ($reset) {
                $channel->data->put('rr.round', 0);
            }
        };

        if ($message == 'reload') {
            $shuffle(true);
            return;
        }

        if ($round > 5) {
            $round = 0;
            $shuffle();
        }

        $response = "The gun clicks";
        $fire = $bullets[$round];

        if ($fire) {
            $response = sprintf("<red>%s dies! D:</red>", $user->nick());
            $shuffle(true);
        } else {
            $channel->data->put('rr.round', ($round + 1));
        }

        $channel->action(sprintf("<i>points the gun at %s</i>  -  <i>*pulls the trigger*</i>  -  <i>%s</i>", $user->nick(), $response));

        $channel->save();
    });