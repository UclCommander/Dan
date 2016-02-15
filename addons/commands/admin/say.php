<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['say', 'msg'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Sends a message to the a interwebs location')
    ->handler(function (Connection $connection, UserContract $user, $message, Channel $channel = null) {
        $data = explode(' ', $message, 2);

        $location = $channel ?? $user;

        if (count($data) != 2 || empty($message)) {
            $location->message('I need something to say!');

            return;
        }

        if (strpos($data[0], ':') !== false) {
            $srv = explode(':', $data[0]);
            $where = $srv[0];
            $chan = $srv[1];

            if (!connection()->hasConnection($where)) {
                $location->message("I'm not connected there.");

                return;
            }

            $connection = connection($where);
            $data[0] = $chan;
        }

        if (!$connection->isChannel($data[0])) {
            $connection->message($data[0], $data[1]);

            return;
        }

        if (!$connection->inChannel($data[0])) {
            $location->message("I'm not in that channel!");

            return;
        }

        $connection->getChannel($data[0])->message($data[1]);
    });
