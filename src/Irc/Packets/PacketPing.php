<?php namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketPing implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        $connection->send("PONG", $data[0]);
    }
}