<?php


use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Pluralizer;

command(['chaninfo', 'cinfo'])
    ->rank('oaq')
    ->helpText([
        'cinfo hooks disabled - Lists disabled hooks',
        'cinfo hooks enable <hook> - Enables a hooks',
        'cinfo hooks disable <hook> - Disables a hooks',
        'cinfo commands disabled - Lists disabled commands',
        'cinfo commands enable <command> - Enables a command',
        'cinfo commands disable <command> - Disables a command',
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
         *
         * @return bool|null
         */
        public function typeHooks(Channel $channel, User $user, array $data)
        {
            if ($data[0] == 'disabled') {
                $list = $channel->getData('info.hooks.disabled', []);
                $channel->message('Disabled hooks: '.implode(', ', $list))->save();

                return null;
            }

            if (!isset($data[1])) {
                $channel->message('Please specify a hook.');

                return null;
            }

            if ($data[0] == 'enable') {
                return $this->doThing($channel, 'hook', $data[1]);
            }

            if ($data[0] == 'disable') {
                return $this->doThing($channel, 'hook', $data[1], false);
            }
        }

        /**
         * @param \Dan\Irc\Location\Channel $channel
         * @param \Dan\Irc\Location\User $user
         * @param array $data
         *
         * @return bool|void
         */
        public function typeCommands(Channel $channel, User $user, array $data)
        {
            if ($data[0] == 'disabled') {
                $list = $channel->getData('info.commands.disabled', []);
                $channel->message('Disabled commands: '.implode(', ', $list))->save();

                return null;
            }

            if (!isset($data[1])) {
                $channel->message('Please specify a command.');

                return null;
            }

            if ($data[0] == 'enable') {
                return $this->doThing($channel, 'command', $data[1]);
            }

            if ($data[0] == 'disable') {
                return $this->doThing($channel, 'command', $data[1], false);
            }

            $channel->message("Invalid command {$data[0]}.");
        }

        /**
         * @param \Dan\Irc\Location\Channel $channel
         * @param $type
         * @param $name
         * @param bool $enable
         *
         * @return bool
         */
        public function doThing(Channel $channel, $type, $name, $enable = true)
        {
            $plual = Pluralizer::plural($type);

            $method = $enable ? 'forgetData' : 'putData';
            $what = $enable ? 'enabled' : 'disabled';
            $type = ucfirst($type);

            $disabled = $channel->getData("info.{$plual}.disabled", []);

            $in = in_array($name, $disabled);
            $in = $enable ? !$in : $in;

            if ($in) {
                $channel->message("{$type} {$name} is already {$what}.");

                return true;
            }

            $channel->$method("info.{$plual}.disabled", $name)
                    ->message("{$type} <b>{$name}</b> has been {$what}.")
                    ->save();

            return true;
        }
    });

