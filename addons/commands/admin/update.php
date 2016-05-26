<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;
use Dan\Update\Updater;

command(['update'])
    ->allowConsole()
    ->allowPrivate()
    ->rank('S')
    ->helpText('Updates the bot.')
    ->handler(function (UserContract $user, Updater $updater, $message, Channel $channel = null) {
        $location = $channel ?? $user;

        try {
            if ($message == 'do') {
                $update = $updater->update(function ($message) use ($location) {
                    $location->message($message);
                });

                if (!$update) {
                    $location->message('No updates found.');
                }

                return;
            }

            if ($updater->check()) {
                $location->message("Updates found. Run <i>update do</i> to install the updates.");

                return;
            }

            $location->message('No updates found.');

        } catch (Exception $e) {
            $location->message($e->getMessage());
        }
    });
