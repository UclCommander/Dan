<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['disconnect'])
    ->allowPrivate()
    ->allowConsole()
    ->rank('S')
    ->helpText('Connects to a network')
    ->handler(function (UserContract $user, $message, Channel $channel = null) {

        $location = $channel ?? $user;

        try {
            if (empty($message)) {
                $message = connection()->getName();
            }

            if ($message == 'console') {
                $func = function ($x) {
                    return connection()->hasConnection($x);
                };

                $location->message('Connected Networks: '.implode(', ', array_filter(array_keys(config('irc.servers')), $func)));

                return;
            }

            if (!connection()->hasConnection($message)) {
                $location->message("Connection {$message} doesn't exist.");

                return;
            }

            if (connection()->removeConnection($message)) {
                $location->message("Disconnecting from {$message}");

                return;
            }

            $location->message("Unable to disconnect from {$message}");
        } catch (Exception $e) {
            console()->exception($e);
        }
    });
