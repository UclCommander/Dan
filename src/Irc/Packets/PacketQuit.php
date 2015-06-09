<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Helpers\Logger;

class PacketQuit implements PacketContract {


    public function handle($from, $data)
    {
        $user = user($from);

        console("[{$data[0]}] {$user->nick()} left IRC");

        foreach(connection()->channels() as $channel)
            $channel->removeUser($user);

        event('irc.packets.quit', [
            'user'      => $user
        ]);
    }
}