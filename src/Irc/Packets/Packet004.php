<?php namespace Dan\Irc\Packets;

use Dan\Irc\Connection;
use Dan\Irc\PacketInterface;
use Dan\Irc\User;

class Packet004 implements PacketInterface {

    public function run(Connection &$connection, array $data, User $user)
    {
        foreach($connection->config['channels'] as $autoJoinChannel)
            $connection->joinChannel($autoJoinChannel[0], (isset($autoJoinChannel[1]) ? $autoJoinChannel[1] : null));
    }
}
 