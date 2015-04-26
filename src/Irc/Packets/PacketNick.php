<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Console\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketNick implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $user = $packetInfo->get('user');
        $nick = $packetInfo->get('command')[0];

        foreach($connection->getChannels() as $channel)
        {
            if ($channel->hasUser($user))
            {
                $temp = $channel->getUser($user);
                $channel->removeUser($temp);
                $temp->setNick($nick);
                $channel->addUser($temp);
            }
        }

        Event::fire('irc.packets.nick', new EventArgs($packetInfo));
        Console::info("[   ] {$user->getNick()} is now know as {$nick}");
    }
}