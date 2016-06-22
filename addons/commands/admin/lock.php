<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['lock'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Locks a channel from interaction with the bot.')
    ->handler(function (UserContract $user, Connection $connection, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (!empty($message)) {
            if (!$connection->inChannel($message)) {
                $location->message("I'm not in that channel!");
            }

            $channel = $connection->getChannel($message);
        }

        $channel->setData('locked', true)->save();
        $location->message('Channel has been locked.');
    });


command(['unlock'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Unlocks a channel from interaction with the bot.')
    ->handler(function (UserContract $user, Connection $connection, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (!empty($message)) {
            if (!$connection->inChannel($message)) {
                $location->message("I'm not in that channel!");
            }

            $channel = $connection->getChannel($message);
        }

        $channel->setData('locked', false)->save();
        $location->message('Channel has been unlocked.');
    });
