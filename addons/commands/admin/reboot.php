<?php

use Dan\Contracts\UserContract;
use Dan\Irc\Location\Channel;

command(['reboot', 'restart'])
    ->allowConsole()
    ->allowPrivate()
    ->rank('S')
    ->helpText('Restarts the bot.')
    ->handler(function (UserContract $user, Channel $channel = null) {
        $location = $channel ?? $user;

        if (!function_exists('pcntl_exec')) {
            $location->notice('Unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.');

            return;
        }

        if (!is_executable(ROOT_DIR.'/dan')) {
            $location->message("Restart failed because the dan file isn't executable.");

            return;
        }
        
        if (!connection()->disconnectFromAll(true)) {
            $location->message('Unable to disconnect from all the connections.');

            return;
        }

        $location->message('Bye!');

        pcntl_exec(ROOT_DIR.'/dan');
    });
