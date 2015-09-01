<?php

use Dan\Core\Dan;
use Dan\Irc\Connection;
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