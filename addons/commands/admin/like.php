<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['clones', 'like'])
    ->helpText([
        'Searches users for same or similar hosts',
    ])
    ->handler(function (Connection $connection, User $user, Channel $channel, $message) {

        /** @var array $users */
        $users = $connection->database('users')->get();

        $matches = [];

        if ($message == null) {
            $message = $user->nick;
        }

        if ($channel->hasUser($message)) {
            $message = '*'.$channel->getUser($message)->host.'*';
        }

        foreach ($users as $nick => $cuser) {
            if (fnmatch($message, $cuser['host']) && $channel->hasUser($cuser['nick'])) {
                $matches[] = "{$cuser['nick']} [{$cuser['host']}]";
            }
        }

        if (count($matches) == 0) {
            $user->notice('No matches found!');
        }

        if (count($matches) > 8) {
            $count = count($matches);
            $matches = array_splice($matches, 0, 7);
            $matches[] = 'and '.($count - 8).' more...';
        }

        foreach ($matches as $match) {
            $user->notice($match);
        }
    });
