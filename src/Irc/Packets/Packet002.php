<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet002 implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        if(!DEBUG)
            console("[{$from[0]}] {$data[1]}");
    }
}