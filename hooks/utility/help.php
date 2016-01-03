<?php

use Dan\Hooks\HookManager;
use Illuminate\Support\Collection;

hook('help')
    ->command(['help', 'commands'])
    ->console()
    ->help('Gets help')
    ->func(function(Collection $args) {
        $hooks = HookManager::getHooks('command');
        $name = $args->get('message');

        $list = [];

        foreach($hooks as $hook)
        {
            $h = $hook->hook();

            if($args->get('console') && !$h->canRunInConsole)
                continue;

            $cmds = $h->commands;

            if($name != null && in_array($name, $cmds))
            {
                foreach($hook->hook()->help as $help)
                    $args->get('user')->notice($help);

                return;
            }

            $first  = array_shift($cmds);
            $cmd    = $first . (count($cmds) > 0 ? " (" . implode(', ', $cmds) . ")" : '');
            $list   = array_merge($list, (array)$cmd);
        }

        sort($list);

        $i = 0;

        $items = [];

        foreach($list as $item)
        {
            if($i == 10)
            {
                $args->get('user')->notice(implode(', ', $items));
                $items = [];
                $i = 0;
                continue;
            }

            $items[] = $item;
            $i++;
        }

        $args->get('user')->notice(implode(', ', $items));
    });