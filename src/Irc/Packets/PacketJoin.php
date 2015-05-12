<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketJoin implements PacketContract {


    public function handle($from, $data)
    {
        $user = user($from);

        if($user->nick() == config('irc.user.nick'))
        {
            if(!connection()->inChannel($data[0]))
            {
                connection()->addChannel($data[0]);
            }
        }

        console("[{$data[0]}] {$user->nick()} joined the channel");

        event('irc.packets.join', [
            'user'      => $user,
            'channel'   => connection()->getChannel($data[0])
        ]);
    }
}