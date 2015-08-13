<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Dan;

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

        if(!connection()->inChannel($data[0]))
            return;

        $channel = connection()->getChannel($data[0]);


        if($channel->getLocation() == config('dan.control_channel'))
        {
            if (!Dan::isAdminOrOwner($user) && $user->nick() != config('irc.user.nick') && $user->host() != connection()->user()->host())
            {
                connection()->send("KICK", config('dan.control_channel'), $user->nick());
                return;
            }
        }

        $channel->setUsers($user->nick());

        event('irc.packets.join', [
            'user'      => $user,
            'channel'   => $channel
        ]);
    }
}