<?php

use Dan\Commands\CommandManager;
use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['reconnect'])
    ->allowPrivate()
    ->allowConsole()
    ->rank('S')
    ->helpText('Reconnects to a network')
    ->handler(function (Connection $connection, UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        try {
            if (empty($message)) {
                $message = $connection->getName();
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

            $location->message("Reconnecting to the network <i>{$message}</i>");

            if (!connection()->removeConnection($message)) {
                $location->message("Unable to disconnect from {$message}");

                return;
            }

            if (dan()->provider(\Dan\Irc\IrcServiceProvider::class)->connect($message)) {
                $location->message('Connected to the network.');

                return;
            }

            $location->message('Error connecting to network.');

        } catch (Exception $e) {
            console()->exception($e);
        }
    });
