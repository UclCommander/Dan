<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketPing implements PacketContract {


    public function handle($from, $data)
    {
        send("PONG", $data[0]);
    }
}