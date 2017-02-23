<?php
/**
Easier reloading of addons & configs
 */
use Dan\Contracts\UserContract;

command(['rehash'])
    ->allowConsole()
    ->allowPrivate()
    ->helpText('Rehashes the bot.')
    ->rank('S')
    ->handler(function (UserContract $user) {
        $user->notice('Reloading addons...');
        dan()->make('addons')->loadAll($user);
        $user->notice('Addons reloaded.');
        $user->notice('Reloading config files...');
        dan('config')->load(); /**Reloading config */
        $user->notice('Config reloaded!');
    });
