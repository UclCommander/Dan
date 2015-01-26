<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketJoin implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        if($packetInfo->get('user')->getNick() === $connection->user->getNick())
            $connection->addChannel($packetInfo->get('command')[0]);

        Event::fire('irc.packets.join', new EventArgs([
            'user'      => $packetInfo->get('user'),
            'channel'   => $connection->getChannel($packetInfo->get('command')[0])
        ]));
    }
}