<?php

use Dan\Contracts\UserContract;
use Dan\Core\Dan;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;

command(['info'])
    ->allowPrivate()
    ->allowConsole()
    ->helpText('Gives information on the bot.')
    ->handler(function (UserContract $user, Channel $channel = null, Connection $connection = null) {
        $location = $channel ?? $user;

        $version = Dan::VERSION;

        $prefix = '/';

        if ($user instanceof \Dan\Irc\Location\User) {
            $prefix = $connection->config->get('command_prefix');
        }

        $location->message("Dan the IRC bot v{$version} by UclCommander. https://links.ml/XOH4 - See <b>{$prefix}help</b> for a list of commands.");
    });
