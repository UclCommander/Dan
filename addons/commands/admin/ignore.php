<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;

command(['ignore'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Ignores a user by nick or hostmask.')
    ->handler(function (Connection $connection, UserContract $user, $message) {

        if (empty($message)) {
            $data = $connection->database('ignore')->get();
            $masks = [];

            if (!$data->count()) {
                $user->notice('Nobody is ignored.');

                return;
            }

            foreach ($data as $mask) {
                $masks[] = $mask['mask'];
            }

            $user->notice(implode(', ', $masks));

            return;
        }

        if ($connection->ignore($message)) {
            $user->notice('User has been ignored.');

            return;
        }

        $user->notice('User is already ignored.');
    });

command(['unignore'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Unignores a user by nick or hostmask.')
    ->handler(function (Connection $connection, UserContract $user, $message) {

        if (empty($message)) {
            $data = $connection->database('ignore')->get();
            $masks = [];

            if (!$data->count()) {
                $user->notice('Nobody is ignored.');

                return;
            }

            foreach ($data as $mask) {
                $masks[] = $mask['mask'];
            }

            $user->notice(implode(', ', $masks));

            return;
        }

        if ($connection->unignore($message)) {
            $user->notice('User has been un-ignored.');

            return;
        }

        $user->notice('User was not ignored in the first place.');
    });
