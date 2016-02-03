<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['join'])
    ->usableInPrivate()
    ->usableInConsole()
    ->requiresIrcConnection()
    ->rank('ASC')
    ->helpText("Joins a channel")
    ->handler(function (Connection $connection, UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (empty($message)) {
            $location->message("Please provide a channel.");
            return;
        }

        $data = explode(':', $message);

        if (!$connection->isChannel($data[0])) {
            $location->message("Channel name is invalid.");
            return;
        }

        $connection->joinChannel($data[0], $data[1] ?? null);
        $location->message("Joining channel {$data[0]}");
    });