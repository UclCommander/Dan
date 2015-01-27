<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketJoin implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        if($packetInfo->get('user')->getNick() === $connection->user->getNick())
            $connection->addChannel($packetInfo->get('command')[0]);

        $user       = $packetInfo->get('user');

        if(!$connection->hasChannel($packetInfo->get('command')[0]))
            return;

        $channel    = $connection->getChannel($packetInfo->get('command')[0]);
        $channel->addUser($user);

        Event::fire('irc.packets.join', new EventArgs([
            'user'      => $user,
            'channel'   => $channel
        ]));

        Console::text("[{$channel->getName()}] {$user->getNick()} joined the channel")->info()->push();
    }
}