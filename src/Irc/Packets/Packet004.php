<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet004 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        $connection->support['SERVERNAME'] = $data[1];
        $connection->support['VERSION'] = $data[2];
        $connection->support['UMODES'] = $data[3];
        $connection->support['CMODES'] = $data[4];
    }
}
