<?php

/**
 * Channel Info command. Modifies channel information. Currently only disables hooks.
 *
 * chaninfo hooks enable <hook> - Enables a disabled hook in the current channel.
 * chaninfo hooks disable <hook> - Disables a hook for the current channel.
 * chaninfo hooks list - List all hooks available to disable.
 * chaninfo hooks disabled - List all disabled hooks.
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Dan\Hooks\HookManager;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('chaninfo')
    ->command(['chaninfo', 'cinfo'])
    ->rank('oaq')
    ->help([
        "Sets channel information and settings.",
        "cinfo hooks enable/disable <hook> - Enables or disables the given hook",
        "cinfo hooks disabled - View all disabled hooks",
        "cinfo hooks list - View all available hooks",
    ])
    ->func(function(Collection $args) {

        $data = explode(' ', $args->get('message'));

        /** @var Channel $channel */
        $channel = $args->get('channel');

        switch($data[0])
        {
            case 'hooks':
            {
                $hooks = [];

                foreach(HookManager::getHooks() as $hook)
                    if(!$hook->isCommand())
                        $hooks[] = $hook->getName();

                sort($hooks);

                $info = database()->table('channels')->where('name', $channel->getLocation())->first()->get('info');
                $except = isset($info['disabled_hooks']) ? $info['disabled_hooks'] : [];

                if(!isset($data[1]))
                {
                    $channel->message("Options: enable <hook>, disable <hook>, disabled, list");
                    return;
                }

                if($data[1] == 'disabled')
                {
                    $channel->message("Disabled hooks: " . implode(', ', $except));
                    return;
                }

                if($data[1] == 'list')
                {
                    $channel->message("Available hooks: " . implode(', ', $hooks));
                    return;
                }

                if($data[1] == 'enable')
                {
                    if(!isset($data[2]))
                    {
                        $channel->message("I need something to enable!");
                        return;
                    }

                    if(!in_array($data[2], $hooks))
                    {
                        $channel->message("This hook doesn't exist!");
                        return;
                    }

                    foreach($except as $i => $item)
                        if($item == $data[2])
                            unset($except[$i]);

                    $channel->message("Hook {$data[2]} has been enabled.");
                }

                if($data[1] == 'disable')
                {
                    if(!isset($data[2]))
                    {
                        $channel->message("I need something to disable!");
                        return;
                    }

                    if(!in_array($data[2], $hooks))
                    {
                        $channel->message("This hook doesn't exist!");
                        return;
                    }

                    if(!Dan::isAdminOrOwner($args->get('user')) && in_array($data[2], ['chaninfo', 'help', 'spy', 'users']))
                    {
                        $channel->message("You're not allowed to disable this hook.");
                        return;
                    }

                    $except[] = $data[2];

                    $channel->message("Hook {$data[2]} has been disabled.");
                }

                database()->table('channels')->where('name', $channel->getLocation())->update([
                    'info' => [
                        'disabled_hooks' => $except
                    ]
                ]);
            }
                break;

            default:
                $channel->message("Available sub-commands: hooks");

        }
    });