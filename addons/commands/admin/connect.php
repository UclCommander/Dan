<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['connect'])
    ->allowPrivate()
    ->allowConsole()
    ->rank('SC')
    ->helpText('Connects to a network')
    ->handler(function (UserContract $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        try {
            if (empty($message)) {
                $location->message("Connected Networks: ".implode(', ', array_filter(array_keys(config('irc.servers')), function ($x) { return connection()->hasConnection($x); })));
                $location->message("Available Networks: ".implode(', ', array_filter(array_keys(config('irc.servers')), function ($x) { return !connection()->hasConnection($x); })));

                return;
            }

            if (!array_key_exists($message, config('irc.servers'))) {
                $location->message("This network has no configuration set.");

                return;
            }

            $location->message("Connecting to the network <i>{$message}</i>");

            if (dan()->provider(\Dan\Irc\IrcServiceProvider::class)->connect($message)) {
                $location->message("Connected to the network.");

                return;
            }

            $location->message("Error connecting to network.");
        } catch (Exception $e) {
            console()->exception($e);
            $location->message("An exception was thrown while connecting to the network. Check console for more details.");
        }
    });