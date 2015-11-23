<?php

/**
 * Says something in a channel, or sends a user a message.
 * Formats:
 *   say Hello!
 *   say #UclCommander hello!
 *   say byteirc:#UclCommander hello!
 *
 * Do not directly edit this file.
 * If you want to change the rank, see commands.permissions in the configuration.
 */

use Dan\Core\Dan;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

hook('say')
    ->command(['msg', 'say'])
    ->console()
    ->rank('AS')
    ->help('Sends a message to a channel')
    ->func(function(Collection $args) {
        /** @var Channel $channel */
        $channel    = $args['channel'];
        /** @var Connection $connection */
        $connection = $args['connection'];
        $data       = explode(' ', $args['message']);

        if(empty($data))
        {
            $channel->message('I need something to say!');
            return;
        }


        if(strpos($data[0], ':') !== false)
        {
            $srv    = explode(':', $data[0]);
            $where  = $srv[0];
            $chan   = $srv[1];

            array_shift($data);

            if(!Dan::hasConnection($where))
            {
                $channel->message("This connection doesn't exist");
                return;
            }

            $conn = Dan::connection($where);

            if(!isChannel($chan, $where))
            {
                $channel->message("Invalid Channel prefix.");
                return;
            }

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
            $name = $data[0];

            if(!$connection->inChannel($name))
            {
                $channel->message("I'm not in that channel!");
                return;
            }

            array_shift($data);

            $connection->getChannel($name)->message(implode(' ', $data));
            return;
        }

        $channel->message(implode(' ', $data));
    });