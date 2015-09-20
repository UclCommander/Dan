<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketQuit implements PacketContract {


    public function handle(Connection $connection, array $from, array $data)
    {
        $user = user($from);

        event('irc.packets.quit', [
            'connection'    => $connection,
            'user'          => $user,
            'message'       => ($data[0] ?? null)
        ]);

       foreach($connection->channels as $channel)
           $channel->removeUser($user);

        if(!DEBUG)
            console("[<magenta>{$connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>left the network</cyan>");
    }
}