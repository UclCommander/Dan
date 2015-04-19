<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketPart implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $user       = $packetInfo->get('user');
        $channel    = $connection->getChannel($packetInfo->get('command')[0]);
        $channel->removeUser($user);

        Event::fire('irc.packets.part', new EventArgs([
            'user'      => $user,
            'channel'   => $channel
        ]));

        Console::text("[{$channel->getName()}] {$user->getNick()} left the channel")->info()->push();

        if($packetInfo->get('user')->getNick() === $connection->user->getNick())
            $connection->removeChannel($packetInfo->get('command')[0]);
    }
}