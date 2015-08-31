<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet353 implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        $channel = $data[2];

        if(!$connection->inChannel($channel))
            return;

        $connection->getChannel($channel)->setUsers($data[3]);
    }
}