<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['kick', 'k'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('oaqAS')
    ->helpText('Kicks a user from the channel')
    ->handler(function (Connection $connection, UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        $data = explode(' ', $message, 3);

        $from = $channel;
        $theUser = $data[0];
        $reason = $data[1] ? implode(' ', [$data[1], ($data[2] ?? null)]) : null;

        if ($connection->isChannel($theUser)) {
            if (!$connection->inChannel($theUser)) {
                $location->message("I'm not in this channel!");
                return;
            }

            $from = $connection->getChannel($theUser);
            $theUser = $data[1];
            $reason = $data[2];
        }

        if ($theUser == $connection->user->nick) {
            $location->message("Hey! That's rude!");
            // TODO: implement slap command here.
            return;
        }

        if (!$from->getUser($connection->user)->hasPermissionTo('kick')) {
            $location->message("I'm not allowed to kick users here.");

            return;
        }

        $from->kick($theUser, trim($reason));
    });