<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketPart implements PacketContract {


    public function handle(Connection $connection, array $from, array $data)
    {
        if($from[0] != $connection->user->nick())
        {
            $channel = $connection->getChannel($data[0]);

            $channel->removeUser($from[0]);
        }
        else
            $connection->removeChannel($data[0]);

        event('irc.packets.part', [
            'user'          => user($from),
            'channel'       => $connection->getChannel($data[0]),
            'connection'    => $connection,
            'message'       => $data[1] ?? null
        ]);

        if(!DEBUG)
            console("[<magenta>{$connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>left {$data[0]}</cyan>");
    }
}