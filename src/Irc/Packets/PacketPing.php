<?php namespace Dan\Irc\Packets; 


use Dan\Irc\Connection;
use Dan\Irc\Packet;
use Dan\Irc\PacketInfo;

class PacketPing extends Packet {


    public function handlePacket(Connection &$connection, PacketInfo $packetInfo)
    {
        $connection->sendRaw("PONG :{$packetInfo->get('data')[0]}");
    }
}