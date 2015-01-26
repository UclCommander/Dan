<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketMode implements PacketContract {


    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $command = $packetInfo->get('command');

        Event::fire('irc.packets.mode', new EventArgs($packetInfo));

        if($command[0] == $connection->user->getNick())
        {
            $connection->user->setMode($command[1]);
            return;
        }

        if(!$connection->hasChannel($command[0]))
            return;

        $channel = $connection->getChannel($command[0]);

        $channel->getUser($command[2])->setMode($command[1]);
    }
}