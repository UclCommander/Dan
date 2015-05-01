<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketPrivmsg implements PacketContract {


    public function handle($from, $data)
    {
        send("PRIVMSG", $data[0], $data[1]);
    }
}