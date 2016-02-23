<?php

use Carbon\Carbon;
use Dan\Events\Event;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

on('irc.message.public')
    ->name('textreplace')
    ->priority(Event::Normal)
    ->handler(new class {
        /**
         * @var array
         */
        protected $messages = [];

        /**
         * @param \Dan\Irc\Connection $connection
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param $message
         *
         * @return bool|null
         */
        public function run(Connection $connection, Channel $channel, User $user, $message)
        {
            if (!preg_match("/^s\/([^\/]+)\/([^\/]+)?(?:\/(g)?)?/i", $message, $matches)) {
                $this->addLine($connection, $channel, $user, $message);
                return null;
            }

            if (count($matches) < 2) {
                return false;
            }

            $key = $connection->getName() . ':' . $channel->getLocation();
            $messages = $this->messages[$key];

            krsort($messages);

            $global = isset($matches[3]) ? $matches[3] == 'g' : false;

            foreach ($messages as $time => $data) {
                $new = preg_replace("/{$matches[1]}/", ($matches[2] ?? ''), $data['message'], ($global ? -1 : 1));

                if ($new == $data['message']) {
                    continue;
                }

                $this->messages[$key][$time]['message'] = $new;

                /** @var Carbon $carbon */
                $carbon = $data['carbon'];
                $ago    = $carbon->diffForHumans();

                $channel->message("[ <cyan>{$ago}</cyan> ] {$data['user']}: {$new}");
                return false;
            }
        }

        /**
         * @param \Dan\Irc\Connection $connection
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param $message
         */
        public function addLine(Connection $connection, Channel $channel, User $user, $message)
        {
            $key = $connection->getName() . ':' . $channel->getLocation();

            foreach ($this->messages as $chan => $lines) {
                if (count($lines) > 30) {
                    array_shift($this->messages[$chan]);
                }
            }

            $this->messages[$key][time()] = [
                'message'   => $message,
                'user'      => $user->nick,
                'carbon'    => new Carbon(),
            ];
        }
    });

