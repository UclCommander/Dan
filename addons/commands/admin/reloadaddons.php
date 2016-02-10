<?php

command(['reloadaddons', 'rla'])
    ->allowConsole()
    ->allowPrivate()
    ->helpText('Reloads all addons')
    ->rank('AOC')
    ->handler(function () {
        dan()->make('addons')->loadAll();
    });
