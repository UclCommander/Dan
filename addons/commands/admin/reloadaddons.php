<?php

use Dan\Contracts\UserContract;

command(['reloadaddons', 'rla'])
    ->allowConsole()
    ->allowPrivate()
    ->helpText('Reloads all addons')
    ->rank('S')
    ->handler(function (UserContract $user) {
        $user->notice('Reloading addons..');

        dan()->make('addons')->loadAll($user);

        $user->notice('Addons reloaded.');
    });
