<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

hook('textreplace')
    ->on('irc.packets.message.public')
    ->anon(new class
    {
        protected $messages = [];

        /**
         * @param \Dan\Events\EventArgs $eventArgs
         * @return null
         */
        public function run(\Dan\Events\EventArgs $eventArgs)
        {
            $message = $eventArgs->get('message');

            /** @var Channel $channel */
            $channel = $eventArgs->get('channel');

            if (!preg_match("/^s\/([^\/]+)\/([^\/]+)?(?:\/(g)?)?/i", $message, $matches)) {
                $this->addLine($channel, $eventArgs->get('user'), $message);

                return null;
            }

            if (count($matches) < 2) {
                return false;
            }

            $messages = $this->messages[$channel->getLocation()];

            krsort($messages);

            $global = isset($matches[3]) ? $matches[3] == 'g' : false;

            foreach ($messages as $time => $data) {
                $new = preg_replace("/{$matches[1]}/", ($matches[2] ?? ''), $data['message'], ($global ? -1 : 1));

                if ($new == $data['message']) {
                    continue;
                }

                $this->messages[$channel->getLocation()][$time]['message'] = $new;

                $carbon = new \Carbon\Carbon();
                $carbon->setTimestamp($time);
                $ago = $carbon->diffForHumans();

                $channel->message("[<cyan>{$ago}</cyan>] {$data['user']}: {$new}");

                return false;
            }
        }

        /**
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param $message
         */
        public function addLine(Channel $channel, User $user, $message)
        {
            foreach ($this->messages as $chan => $lines) {
                if (count($lines) > 30) {
                    array_shift($this->messages[$chan]);
                }
            }

            $this->messages[$channel->getLocation()][time()] = [
                'message' => $message,
                'user'    => $user->getLocation(),
            ];
        }
    });