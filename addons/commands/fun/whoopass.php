<?php

/**
 * Whoopass command. When a normal beating just won't do!
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command('whoopass')
    ->helpText('When a normal beating just won\'t do!')
    ->handler(function (Connection $connection, User $user, $message, Channel $channel) {
        $data = explode(' ', $message);

        if ($data[0] && $user->hasPermissionTo('kick')) {
            if (!$channel->getUser($connection->user)->hasPermissionTo('kick')) {
                $user->notice('I\'m not allowed to kick users here.');

                return;
            }

            $channel->kick($data[0], 'When a normal beating just won\'t do! WHOOPASS! Extra strength! http://skycld.co/whoopass');

            return;
        }

        $channel->message('When a normal beating just won\'t do! http://skycld.co/whoopass');
    });
