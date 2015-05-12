<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketPart implements PacketContract {


    public function handle($from, $data)
    {
        $user = user($from);

        console("[{$data[0]}] {$user->nick()} left the channel");

        event('irc.packets.part', [
            'user'      => $user,
            'channel'   => connection()->getChannel($data[0])
        ]);
    }
}