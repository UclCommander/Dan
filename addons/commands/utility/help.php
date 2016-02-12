<?php

use Dan\Commands\Command;
use Dan\Commands\CommandManager;
use Dan\Contracts\UserContract;
use Dan\Irc\Connection;

command(['help', 'commands'])
    ->allowConsole()
    ->allowPrivate()
    ->helpText('Gets help')
    ->handler(function (CommandManager $commandManager, UserContract $user, $message, Connection $connection = null) {
        $commands = $commandManager->commands();
        $list = [];

        foreach ($commands as $command) {
            /* @var Command $command */

            $aliases = $command->getAliases();

            if (!is_null($connection)) {
                if (!$commandManager->canUseCommand($connection, $command, $user)) {
                    continue;
                }
            }

            if ($message != null && in_array($message, $aliases)) {
                foreach ($command->getHelpText() as $help) {
                    $user->notice($help);
                }

                return;
            }

            $first = array_shift($aliases);
            $cmd = $first.(count($aliases) > 0 ? ' ('.implode(', ', $aliases).')' : '');
            $list = array_merge($list, [$cmd]);
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

        $user->notice(implode(', ', $items));
    });
