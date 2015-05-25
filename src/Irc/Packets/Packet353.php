<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet353 implements PacketContract {


    public function handle($from, $data)
    {
        $channel = $data[2];

        if(!connection()->inChannel($channel))
            return;

        connection()->getChannel($channel)->setUsers($data[3]);
    }
}