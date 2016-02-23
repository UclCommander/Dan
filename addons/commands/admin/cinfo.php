<?php


use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

command(['chaninfo', 'cinfo'])
    ->rank('oaq')
    ->helpText([
        'test'
    ])
    ->handler(new class {

        /**
         * @var Connection
         */
        protected $connection;

        /**
         * @param \Dan\Irc\Connection $connection
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param $message
         *
         * @return bool
         */
        public function run(Connection $connection, Channel $channel, User $user, $message)
        {
            $this->connection = $connection;

            if (empty($message)) {
                return false;
            }

            $data = explode(' ', $message);
            $name = $data[0];
            $method = 'type' . ucfirst(strtolower($name));

            if (method_exists($this, $method)) {
                array_shift($data);
                return $this->$method($channel, $user, $data);
            }

            $channel->message("Invalid command {$name}");

            return null;
        }

        /**
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param array $data
         */
        public function typeHooks(Channel $channel, User $user, array $data)
        {
            // TODO
        }

        /**
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param array $data
         */
        public function typeCommands(Channel $channel, User $user, array $data)
        {
            if ($data[0] == 'disabled') {
                $list = $channel->getData('info.commands.disabled', []);
                $channel->message('Disabled commands: '.implode(', ', $list))->save();

                return;
            }

            if (!isset($data[1])) {
                $channel->message('Please specify a command');

                return;
            }

            if ($data[0] == 'enable') {
                $channel->forgetData('info.commands.disabled', $data[1])
                    ->message("Command <i>{$data[1]}</i> has been enabled.")
                    ->save();

                return;
            }

            if ($data[0] == 'disable') {
                $channel->putData('info.commands.disabled', $data[1])
                    ->message("Command <i>{$data[1]}</i> has been disabled.")
                    ->save();

                return;
            }

            $channel->message("Invalid command {$data[0]}");
        }
    });

