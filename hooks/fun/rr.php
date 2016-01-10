<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

$bullets = [false, false, false, true, false, false];
shuffle($bullets);
$shots = 0;

hook('roulette')
    ->command(['roulette', 'rr'])
    ->help("yeah, you know what this is.")
    ->func(function(Collection $args) use (&$bullets, &$shots) {
        /** @var User $user */
        $user = $args->get('user');

        /** @var Channel $channel */
        $channel = $args->get('channel');

        $message = $args->get('message');

        if ($message == 'stats') {
            $stats = $channel->info('rr.deaths');
            $count = count($stats);

            asort($stats);
            reset($stats);
            $key = key($stats);

            $channel->message("A total of {$count} people have died playing russian roulette. {$key} has died the most with {$stats[$key]} deaths.");
            return;
        }

        if ($channel->hasUser($message)) {
            $user = $channel->getUser($message);
        }

        $response = "The gun clicks";

        if ($bullets[$shots]) {
            $response = sprintf("<red>%s dies! D:</red>", $user->nick());

            shuffle($bullets);
            $shots = 0;

            $prev = $channel->info("rr.deaths") ?? 0;
            $prev[$user->nick()] += $prev[$user->nick()] + 1;
            $channel->setInfo('rr', ['deaths' => $prev]);
        }

        $channel->action(sprintf("<i>points the gun at %s</i>  -  <i>*pulls the trigger*</i>  -  <i>%s</i>", $user->nick(), $response));

        $shots++;
    });