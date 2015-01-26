<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;
use Dan\Storage\Storage;

class Packet352 implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $command = $packetInfo->get('command');

        Event::fire('irc.packets.who', new EventArgs($packetInfo));

        if($command[0] == $connection->user->getNick())
        {
            $connection->user->setHost($command[3]);
            $connection->user->setRealName($command[7]);
        }

        $storage = new Storage('users');

        $storage->add($command[5], [
            'nick'     => $command[5],
            'user'     => $command[2],
            'host'     => $command[3],
            'realName' => explode(' ', $command[7], 2)[1]
        ])->save();
    }

}