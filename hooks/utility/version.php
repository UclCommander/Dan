<?php

/**
 * Gets the current version of dan.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('version')
    ->command(['version', 'v'])
    ->help('Gets the current version')
    ->func(function(Collection $args) {
        $v = Dan::getCurrentGitVersion();

        $args->get('channel')->message("[ <cyan>Version</cyan> <yellow>{$v['id']}</yellow> | <cyan>{$v['message']}</cyan> ]");
    });
