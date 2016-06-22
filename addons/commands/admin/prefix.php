<?php

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['prefix'])
    ->helpText([
        'Changes the command prefix',
    ])
    ->rank('AS')
    ->requiresIrcConnection()
    ->allowPrivate()
    ->handler(function (Connection $connection, User $user, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        if (strlen(trim($message)) !== 1) {
            $location->message('Command prefixes should only be one character.');

            return;
        }

        $prefix = substr(trim($message), 0, 1);

        config()->set("irc.servers.{$connection->getName()}.command_prefix", $prefix);

        $location->message("Prefix is now set to {$prefix}");
    });
