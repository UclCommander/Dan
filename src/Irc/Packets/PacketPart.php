<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketPart implements PacketContract {


    public function handle($from, $data)
    {
        $user = user($from);

        console("[{$data[0]}] {$user->nick()} left the channel");

        if(!connection()->inChannel($data[0]))
            return;

        $channel = connection()->getChannel($data[0]);
        $channel->removeUser($user);

        if($user->user() == connection()->user()->nick())
            connection()->removeChannel($channel->getLocation());

        event('irc.packets.part', [
            'user'      => $user,
            'channel'   => $channel
        ]);
    }
}