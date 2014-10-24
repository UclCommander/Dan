<?php namespace Dan\Irc\Packets; 


use Dan\Irc\Connection;
use Dan\Irc\PacketInterface;
use Dan\Irc\User;

class PacketNotice implements PacketInterface {


    public function run(Connection &$connection, array $data, User $user)
    {
        // TODO: Implement run() method.
    }
}
 