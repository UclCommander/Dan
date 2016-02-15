<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['op', 'deop'])
    ->allowConsole()
    ->allowPrivate()
    ->requiresIrcConnection()
    ->rank('oaqAS')
    ->helpText('Ops or de-ops a user')
    ->handler(function (Connection $connection, UserContract $user, $message, $command, Channel $channel = null) {
        $toBeOped = $message;
        $data = explode(' ', $message);

        $notify = $channel ?? $user;

        if (count($data) == 0) {
            $notify->message('I need someone to op.');

            return;
        }

        if (is_null($channel)) {
            if (!$connection->inChannel($data[0])) {
                $notify->message("I'm not in that channel.");

                return;
            }

            if (!isset($data[1])) {
                $notify->message('I need someone to op.');

                return;
            }

            $channel = $connection->getChannel($data[0]);
            $toBeOped = $data[1];
        }

        if (!$channel->getUser($connection->user)->hasPermissionTo('op')) {
            $notify->message("I'm not allowed to op users in that channel.");

            return;
        }

        if (!$channel->hasUser($toBeOped)) {
            $notify->message("That user isn't in this channel.");

            return;
        }

        $channel->mode(($command == 'op' ? '+o' : '-o'), $toBeOped);
    });
