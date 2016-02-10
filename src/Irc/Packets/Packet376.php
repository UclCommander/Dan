<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet376 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        if (!config('dan.debug') && $connection->config->get('show_motd')) {
            console()->message(end($data));
        }
    }
}