<?php

use Dan\Irc\Connection;

command(['raw'])
    ->usableInPrivate()
    ->usableInConsole()
    ->requiresIrcConnection()
    ->rank('SC')
    ->helpText('Sends a RAW message.')
    ->handler(function (Connection $connection, $message) {
        $connection->raw($message);
    });
