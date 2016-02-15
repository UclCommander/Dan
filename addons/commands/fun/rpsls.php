<?php

/**
 * Rock Paper Scissors Lizard Spock.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['rpsls', 'rock', 'paper', 'scissors', 'lizard', 'spock'])
    ->allowPrivate()
    ->helpText('Rock Paper Scissors Lizard Spock - I challenge you to a duel! https://youtu.be/x5Q6-wMx-K8')
    ->handler(function (User $user, $command, $message, Channel $channel = null) {
        $location = $channel ?? $user;
        $items = [
            'rock'     => ['scissors', 'lizard'],
            'paper'    => ['rock', 'spock'],
            'scissors' => ['paper', 'lizard'],
            'lizard'   => ['spock', 'paper'],
            'spock'    => ['rock', 'scissors'],
        ];

        if (in_array($command, array_keys($items))) {
            $message = $command;
        }

        if (!array_key_exists($message, $items)) {
            $location->message('Invalid choice. Pick from: '.implode(', ', array_keys($items)));

            return;
        }

        $randItem = array_random(array_keys($items));

        if ($randItem == $message) {
            $location->message('It\'s a <yellow>TIE!</yellow> Try again!');

            return;
        }

        if (!in_array($randItem, $items[$message])) {
            $location->message("You <red>LOST!</red> >:) - I chose <orange>{$randItem}</orange>");

            return;
        }

        $location->message("You <green>WON!</green> D: - I chose <orange>{$randItem}</orange>");
    });
