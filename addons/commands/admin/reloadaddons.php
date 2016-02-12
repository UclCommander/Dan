<?php

command(['reloadaddons', 'rla'])
    ->allowConsole()
    ->allowPrivate()
    ->helpText('Reloads all addons')
    ->rank('S')
    ->handler(function () {
        dan()->make('addons')->loadAll();
    });
