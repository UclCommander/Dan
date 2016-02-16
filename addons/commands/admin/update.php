<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;
use Dan\Update\Updater;

command(['update'])
    ->allowConsole()
    ->allowPrivate()
    ->rank('S')
    ->helpText('Updates the bot.')
    ->handler(function (UserContract $user, Updater $updater, Channel $channel = null) {
        $location = $channel ?? $user;

        $update = $updater->update(true, function($message) use ($location) {
            $location->message($message);
        });

        if (!$update) {
            $location->message('No updates found.');
        }
    });
