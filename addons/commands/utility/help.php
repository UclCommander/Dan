<?php

use Dan\Commands\Command;
use Dan\Commands\CommandManager;
use Dan\Irc\Location\User;

command(['help', 'commands'])
    ->usableInConsole()
    ->usableInPrivate()
    ->helpText('Gets help')
    ->handler(function (CommandManager $commandManager, User $user, $message) {
        $commands = $commandManager->commands();
        $list = [];

        foreach ($commands as $command) {
            /* @var Command $command */

            $aliases = $command->getAliases();

            var_dump($aliases, $message);

            if ($message != null && in_array($message, $aliases)) {
                foreach ($command->getHelpText() as $help) {
                    $user->notice($help);
                }

                return;
            }

            $first = array_shift($aliases);
            $cmd = $first.(count($aliases) > 0 ? ' ('.implode(', ', $aliases).')' : '');
            $list = array_merge($list, (array) $cmd);
        }

        $i = 0;

        $items = [];

        foreach ($list as $item) {
            if ($i == 10) {
                $user->notice(implode(', ', $items));
                $items = [];
                $i = 0;
                continue;
            }

            $items[] = $item;
            $i++;
        }

        $user->notice($commands->keys()->implode(', '));
    });
