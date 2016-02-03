<?php

command(['reloadaddons', 'rla'])
    ->usableInConsole()
    ->usableInPrivate()
    ->helpText("Reloads all addons")
    ->rank('AOC')
    ->handler(function() {
        dan()->make('addons')->loadAll();
    });