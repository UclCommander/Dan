<?php namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;

class PacketPing implements PacketContract {

    public function handle($from, $data)
    {
        event('irc.packets.ping');
        send("PONG", $data[0]);
    }
}