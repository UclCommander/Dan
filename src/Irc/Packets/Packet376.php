<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet376 implements PacketContract {


    public function handle($from, $data)
    {
        $channels = config('irc.channels');

        foreach($channels as $channel)
            send("JOIN", $channel);
    }
}