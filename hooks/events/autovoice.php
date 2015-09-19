<?php

/**
 * Autovoice hook. Automagically voices registered users on join.
 *
 * Do not directly edit this file.
 */

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

hook('autovoice')
    ->on(['irc.packets.join', 'irc.packets.notice.private'])
    ->anon(new class {
        /**
         * @var Channel[]
         */
        protected $cache = [];

        /**
         * @var array
         */
        protected $logged = [];

        /**
         * @param \Illuminate\Support\Collection $args
         */
        function run(Collection $args){
            /** @var Connection $connection */
            $connection = $args->get('connection');
            /** @var User $user */
            $user       = $args->get('user');

            $message    = $args->get('message');

            if($args->get('event') == 'irc.packets.join')
            {
                /** @var Channel $channel */
                $channel    = $args->get('channel');

                if(array_key_exists($user->nick(), $this->logged))
                {
                    $channel->userMode($user, '+v');
                    return;
                }

                $this->cache[$user->nick()] = $channel;
                $connection->message('NickServ', "info {$user->nick()}");
            }

            if($args->get('event') == 'irc.packets.notice.private')
            {
                foreach($this->cache as $nick => $channel)
                {
                    if($user->nick() != 'NickServ')
                        return;

                    if(strpos($message, $nick) === false)
                        return;

                    if(strpos($message, 'registered') !== false)
                    {
                        unset($this->cache[$nick]);
                        return;
                    }

                    $channel->userMode($nick, '+v');

                    $this->logged[$nick] = true;
                    unset($this->cache[$nick]);
                }
            }
        }
    });