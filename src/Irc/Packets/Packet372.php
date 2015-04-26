<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Console\Console;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class Packet372 implements PacketContract {


    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        if($connection->config->get('show_motd') === true)
            Console::info($packetInfo->get('command')[1]);
    }
}