<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketKick implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $kicker     = $packetInfo->get('user');
        $info       = $packetInfo->get('command');
        $channel    = $connection->getChannel($info[0]);
        $user       = $channel->getUser($info[1]);

        $channel->removeUser($user);

        Event::fire('irc.packets.kick', new EventArgs($packetInfo));
        Console::text("[   ] {$kicker->getNick()} kicked {$user->getNick()} ({$packetInfo->get('command')[2]})")->info()->push();
    }
}