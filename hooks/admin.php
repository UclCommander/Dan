<?php

use Dan\Core\Dan;
use Dan\Hooks\HookManager;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

/**
 * Quit hook. Makes the bot quit.
 */
hook('quit')
    ->command(['quit'])
    ->console()
    ->rank('S')
    ->help('Makes the bot quit')
    ->func(function(Collection $args) {
        Dan::quit($args['message']);
    });

/**
 * Restart hook. Makes the bot quit.
 */
hook('restart')
    ->command(['restart', 'reload', 'reboot'])
    ->console()
    ->rank('S')
    ->help('Makes the bot restart')
    ->func(function(Collection $args) {
        if(!function_exists('pcntl_exec'))
        {
            message($args['channel'], "Unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.");
            return;
        }

        Dan::quit("Restarting bot.");
        pcntl_exec(ROOT_DIR . '/dan');
        return;
    });

/**
 * Says something in a channel.
 * Formats:
 *   say Hello!
 *   say #UclCommander hello!
 *   say byteirc:#UclCommander hello!
 */
hook('say')
    ->command(['msg', 'say'])
    ->console()
    ->rank('AS')
    ->help('Sends a message to a channel')
    ->func(function(Collection $args) {
        $channel    = $args['channel'];
        /** @var Connection $connection */
        $connection = $args['connection'];
        $data       = explode(' ', $args['message']);

        if(empty($data))
        {
            $channel->message('I need something to say!');
            return;
        }

        $types = preg_quote($connection->support->get('CHANTYPES'));

        preg_match("/([a-z]+)\:([{$types}][a-zA-Z0-9_\-\.]+)/", $data[0], $matches);

        if(count($matches) == 3)
        {
            $where = $matches[1];
            $chan = $matches[2];

            array_shift($data);

            if(!Dan::hasConnection($where))
            {
                $channel->message("This connection doesn't exist");
                return;
            }

            $conn = Dan::connection($where);

            if(!$conn->inChannel($chan))
            {
                $channel->message("I'm not in that channel!");
                return;
            }

            $conn->message($chan, implode(' ', $data));
            return;
        }

        if(isChannel($data[0]))
        {
            if(!$connection->inChannel($data[0]))
            {
                $channel->message("I'm not in that channel!");
                return;
            }

            array_shift($data);

            $connection->getChannel($data[0])->message(implode(' ', $data));
            return;
        }

        $channel->message(implode(' ', $data));
    });


hook('join')
    ->command(['join', 'j'])
    ->console()
    ->rank('AS')
    ->help("Joins a channel")
    ->func(function(Collection $args) {
        $args->get('connection')->joinChannel($args->get('message'));
    });

hook('part')
    ->command(['part', 'leave', 'p'])
    ->console()
    ->rank('AS')
    ->help("Leaves a channel")
    ->func(function(Collection $args) {
        $partFrom   = explode(' ', $args->get('message'));
        $chan       = $args->get('channel')->getLocation();
        $reason     = $args->get('message');

        if(isChannel($partFrom[0]))
        {
            $chan   = $partFrom[0];
            $reason = isset($partFrom[1]) ? $partFrom[1] : null;
        }

        if(!connection()->inChannel($chan))
        {
            $args->get('channel')->message("I'm not in this channel!");
            return;
        }

        $args->get('connection')->partChannel($chan, $reason);
    });


hook('chaninfo')
    ->command(['chaninfo', 'cinfo'])
    ->rank('oaq')
    ->help('Sets channel info. Available sub-commands: hooks')
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