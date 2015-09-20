<?php

/**
 * NBC Command. For those moments when you just don't care.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Illuminate\Support\Collection;

hook('nbc')
    ->command(['nbc'])
    ->help('NOBODY CARS THAT YOU NEED HELP')
    ->func(function(Collection $args) {
        $message = $args->get('message');

        $args->get('channel')->message(($message ? "{$message}: " : '') . "http://skycld.co/nbc");
    });