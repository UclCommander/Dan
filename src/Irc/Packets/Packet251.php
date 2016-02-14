<?php

namespace Dan\Irc\Packets;

class Packet251 extends Packet
{
    public function handle(array $from, array $data)
    {
        foreach ($this->connection->config->get('autorun_commands', []) as $command) {
            $nick = $this->connection->user->nick;
            $this->connection->raw(str_replace(['{NICK}'], [$nick], $command));
        }

        if ($this->connection->config->get('user.pass') != '') {
            $password = $this->connection->config->get('user.pass');
            $this->connection->message('NickServ', "IDENTIFY {$password}");
        }

        sleep(5);

        foreach ($this->connection->config->get('channels', []) as $channel) {
            $data = explode(':', $channel);

            $this->connection->joinChannel($data[0], (isset($data[1]) ? $data[1] : null));
        }
    }
}
