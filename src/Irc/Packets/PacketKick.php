<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class PacketKick implements PacketContract {

    public function handle($from, $data)
    {
        $user = user($from);

        console("[{$data[0]}] {$user->nick()} was kicked from the channel");

        if(!connection()->inChannel($data[0]))
            return;

        $channel = connection()->getChannel($data[0]);

        $channel->removeUser($user);

        event('irc.packets.kick', [
            'user'      => $user,
            'channel'   => $channel
        ]);
    }
}