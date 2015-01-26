<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class Packet004 implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $connection->numeric->put('004', $packetInfo->get('command'));
    }
}