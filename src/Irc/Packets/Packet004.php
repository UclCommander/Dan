<?php namespace Dan\Irc\Packets; 


use Dan\Irc\Connection;
use Dan\Irc\Packet;
use Dan\Irc\PacketInfo;

class Packet004 extends Packet {

    public function handlePacket(Connection &$connection, PacketInfo $packetInfo)
    {
        $connection->numeric->put('004', $packetInfo->get('data'));
    }
}