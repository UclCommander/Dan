<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Contracts\UserContract;

command(['part', 'leave'])
    ->usableInPrivate()
    ->usableInConsole()
    ->requiresIrcConnection()
    ->rank('ASC')
    ->helpText("Leaves a channel")
    ->handler(function (Connection $connection, UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (empty($message)) {
            if (is_null($channel)) {
                $location->message('Please provide a channel.');
                return;
            }

            $message = $channel->getLocation();
        }

        if (!$connection->isChannel($message)) {
            $location->message('Channel name is invalid.');
            return;
        }

        if (!$connection->inChannel($message)) {
            $location->message("I'm not in that channel!");
            return;
        }

        $connection->partChannel($message, 'Requested');

        if ($location->getLocation() != $message) {
            $location->message("Parted channel {$message}");
        }
    });