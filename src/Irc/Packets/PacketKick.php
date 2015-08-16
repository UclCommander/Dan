<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class PacketKick implements PacketContract {

    public function handle($from, $data)
    {
        $user = user($from);

        console("[{$data[0]}] {$data[1]} was kicked from the channel");

        if(!connection()->inChannel($data[0]))
            return;

        $channel = connection()->getChannel($data[0]);
        $channel->removeUser($data[1]);

        if($data[1] == connection()->user()->nick())
            connection()->removeChannel($channel->getLocation());

        event('irc.packets.kick', [
            'user'      => $user,
            'channel'   => $channel,
            'kicked'    => $data[1],
        ]);
    }
}