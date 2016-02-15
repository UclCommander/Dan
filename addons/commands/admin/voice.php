<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['voice', 'devoice'])
    ->allowConsole()
    ->allowPrivate()
    ->requiresIrcConnection()
    ->rank('hoaqAS')
    ->helpText('Voices or devoices a user')
    ->handler(function (Connection $connection, UserContract $user, $message, $command, Channel $channel = null) {
        $toBeVoiced = $message;
        $data = explode(' ', $message);

        $notify = $channel ?? $user;

        if (count($data) == 0) {
            $notify->message('I need someone to voice.');

            return;
        }

        if (is_null($channel)) {
            if (!$connection->inChannel($data[0])) {
                $notify->message("I'm not in that channel.");

                return;
            }

            if (!isset($data[1])) {
                $notify->message('I need someone to voice.');

                return;
            }

            $channel = $connection->getChannel($data[0]);
            $toBeVoiced = $data[1];
        }

        if (!$channel->getUser($connection->user)->hasPermissionTo('voice')) {
            $notify->message("I'm not allowed to voice users in that channel.");

            return;
        }

        if (!$channel->hasUser($toBeVoiced)) {
            $notify->message("That user isn't in this channel.");

            return;
        }

        $channel->mode(($command == 'voice' ? '+v' : '-v'), $toBeVoiced);
    });
