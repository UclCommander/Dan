<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketMode implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        if($data[0] == $connection->user->nick())
        {
            $connection->user->setMode($data[1]);
            return;
        }
    }
}
