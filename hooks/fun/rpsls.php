<?php

/**
 * Rock Paper Scissors Lizard Spock.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('rpsls')
    ->command(['rpsls'])
    ->console()
    ->help('Rock Paper Scissors Lizard Spock')
    ->func(function(Collection $args) {
        $items = [
            'rock' => ['scissors', 'lizzard'],
            'paper' => ['rock', 'spock'],
            'scissors' => ['paper', 'lizzard'],
            'lizard' => ['spock', 'papeer'],
            'spock' => ['rock', 'scissors'],
        ];

        $message = $args->get('message');

        if (!array_key_exists($message, $items)) {
            $args->get('channel')->message('Invalid choice. Pick from: ' . implode(', ', array_keys($items)));
            return;
        }

        $rand = array_random(array_keys($items));

        if ($rand == $message) {
            $args->get('channel')->message("It's a <yellow>TIE!</yellow> Try again!");
            return;
        }

        if (!in_array($rand, $items[$message])) {
            $args->get('channel')->message("You <red>LOST!</red> >:) - I chose <orange>{$rand}</orange>");
            return;
        }

        $args->get('channel')->message("You <green>WON!</green> D: - I chose <orange>{$rand}</orange>");
    });