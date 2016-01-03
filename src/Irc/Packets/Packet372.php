<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet372 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        if (!DEBUG && config('irc.show_motd')) {
            console(end($data));
        }
    }
}
