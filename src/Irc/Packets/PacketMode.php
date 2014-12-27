<?php namespace Dan\Irc\Packets; 


use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\Packet;
use Dan\Irc\PacketInfo;

class PacketMode extends Packet {


    public function handlePacket(Connection &$connection, PacketInfo $packetInfo)
    {
        Event::fire('irc.packet.mode', new EventArgs($packetInfo->toArray()));
    }
}