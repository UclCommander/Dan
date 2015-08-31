<?php

use Illuminate\Support\Collection;

/**
 * Reloads all bot hooks.
 */
hook('reloadhooks')
    ->command(['reloadhooks', 'rlh'])
    ->console()
    ->rank('S')
    ->help('Reloads bot hooks')
    ->func(function(Collection $args) {
        \Dan\Hooks\HookManager::loadHooks();

        $args['channel']->message('Hooks reloaded');
    });