<?php namespace Dan\Irc\Packets;

use Dan\Core\Config;
use Dan\Irc\Connection;
use Dan\Irc\PacketInterface;
use Dan\Irc\User;

class PacketJoin implements PacketInterface {

    public function run(Connection &$connection, array $data, User $user)
    {
        if($user->getNick() == Config::get('irc.nickname'))
        {
            $connection->addChannel($data[0]);
        }
    }
}
 