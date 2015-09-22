<?php

/**
 * Reloads all hooks.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('reloadhooks')
    ->command(['reloadhooks', 'reload', 'rlh'])
    ->console()
    ->rank('S')
    ->help('Reloads bot hooks')
    ->func(function(Collection $args) {
        \Dan\Hooks\HookManager::loadHooks();

        $args['channel']->message('Hooks reloaded');
    });