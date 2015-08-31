<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketNotice implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {

    }
}