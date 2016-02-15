<?php

/**
 * Russian Roulette. Live or die...
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['roulette', 'rr'])
    ->allowPrivate()
    ->helpText('Live or die...')
    ->handler(function (User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $round = $location->getData('rr.round', 0);
        $bullets = $user->getData('rr.bullets', [false, false, false, true, false, false]);

        $shuffle = function () use ($location, &$bullets) {
            shuffle($bullets);
            $location->setData('rr.bullets', $bullets);
            $location->setData('rr.round', 0);
            $location->save();
        };

        if ($message == 'reload') {
            $shuffle();

            return;
        }

        if ($round > 5) {
            $shuffle();
        }

        $response = 'The gun clicks...';
        $fire = $bullets[$round];

        if ($fire) {
            $response = sprintf('<red>%s dies! D:</red>', $user->nick);
            $shuffle();
        } else {
            $location->setData('rr.round', ($round + 1));
        }

        $location->action(sprintf('<i>points the gun at %s</i> - <i>*pulls the trigger*</i> - <i>%s</i>', $user->nick, $response));

        $location->save();
    });
