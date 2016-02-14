<?php

use Dan\Irc\Connection;

command(['raw'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('S')
    ->helpText('Sends a RAW message.')
    ->handler(function (Connection $connection, $message) {
        $connection->raw($message);
    });
