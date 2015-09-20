<?php

/**
 * Gets information on the bot.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Illuminate\Support\Collection;

hook('info')
    ->command(['info'])
    ->help('Gives information on the bot.')
    ->func(function(Collection $args) {
        $version = Dan::VERSION;

        $v = Dan::getCurrentGitVersion();
        $version .= " ({$v['id']})";

        $prefix = $args->get('connection')->config->get('command_prefix');

        $args->get('channel')->message("Dan the IRC bot v{$version} by UclCommander. http://skycld.co/dan - See <b>{$prefix}help</b> for a list of commands.");
    });