<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class PacketTopic implements PacketContract {

    public function handle($from, $data)
    {
        if(!connection()->inChannel($data[0]))
            return;

        $channel = connection()->getChannel($data[0]);

        event('irc.packets.title', [
            'user'      => user($from),
            'channel'   => $channel,
            'topic'     => $data[1],
        ]);
    }
}

