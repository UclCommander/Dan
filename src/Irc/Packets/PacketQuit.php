<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Console\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketQuit implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $channels   = $connection->getChannels();
        $user       = $packetInfo->get('user');

        foreach($channels as $channel)
            $channel->removeUser($user);

        Event::fire('irc.packets.quit', new EventArgs($packetInfo));
        Console::info("[   ] {$user->getNick()} quit IRC ({$packetInfo->get('command')[0]})");
    }
}