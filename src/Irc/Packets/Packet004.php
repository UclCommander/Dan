<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class Packet004 implements PacketContract {

    public function handle($from, $data)
    {
        connection()->setNumeric('004', $data);
    }
}