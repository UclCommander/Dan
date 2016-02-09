<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['disconnect'])
    ->usableInPrivate()
    ->usableInConsole()
    ->rank('SC')
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

                $location->message("Connected Networks: ".implode(', ', array_filter(array_keys(config('irc.servers')), $func)));

                return;
            }

            if (!connection()->hasConnection($message)) {
                $location->message("Connection {$message} doesn't exist.");

                return;
            }

            $location->message("Disconnecting from {$message}");
            connection($message)->disconnect();

        } catch (Exception $e) {
            console()->exception($e);
        }
    });