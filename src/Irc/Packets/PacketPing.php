<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketPing implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $connection->sendRaw("PONG {$packetInfo->get('command')[0]}");
    }
}