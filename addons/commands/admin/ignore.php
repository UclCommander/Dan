<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Connection;

command(['ignore'])
    ->allowPrivate()
    ->allowConsole()
    ->requiresIrcConnection()
    ->rank('AS')
    ->helpText('Ignores a user')
    ->handler(function (Connection $connection, UserContract $user, $message) {

        if (empty($message)) {
            $ignored = $connection->database('ignore')->get();

            if (!$ignored->count()) {
                $user->notice('No users are ignored');
                return;
            }

            $masks = [];

            foreach ($ignored as $mask) {
                $masks[] = $mask['mask'];
            }

            $user->notice(implode(', ', $masks));
            return;
        }

        $connection->database('ignore')->insertOrUpdate(['mask', $message], [
            'mask' => $message,
        ]);
    });
