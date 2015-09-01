<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketKick implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        if($from[0] != $connection->user->nick())
        {
            $channel = $connection->getChannel($data[0]);

            $channel->removeUser($data[1]);
        }
        else
            $connection->removeChannel($data[0]);

        if(!DEBUG)
            console("[<magenta>{$connection->getName()}</magenta>] <yellow>{$data[0]}</yellow> <cyan>was kicked from {$data[0]} by <yellow>{$from[0]}</yellow></cyan>");
    }
}