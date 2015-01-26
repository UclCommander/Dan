<?php namespace Dan\Contracts; 


use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

interface PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo);
}