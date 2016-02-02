<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet004 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        $connection->serverInfo->put('server_name', $data[1]);
        $connection->serverInfo->put('version', $data[2]);
        $connection->serverInfo->put('user_modes', $data[3]);
        $connection->serverInfo->put('channel_modes', $data[4]);
    }
}
