<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet315 implements PacketContract {

    public function handle(Connection $connection, array $from, array $data){}
}