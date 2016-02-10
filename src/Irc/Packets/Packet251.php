<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet251 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        foreach ($connection->config->get('autorun_commands', []) as $command) {
            $nick = $connection->user->nick;
            $connection->raw(str_replace(['{NICK}'], [$nick], $command));
        }

        if ($connection->config->get('user.pass') != '') {
            $password = $connection->config->get('user.pass');
            $connection->message('NickServ', "IDENTIFY {$password}");
        }

        sleep(5);

        foreach ($connection->config->get('channels', []) as $channel) {
            $data = explode(':', $channel);

            $connection->joinChannel($data[0], (isset($data[1]) ? $data[1] : null));
        }
    }
}
