<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet353 implements PacketContract {


    public function handle($from, $data)
    {
        $channel = $data[2];

        if(!connection()->inChannel($channel))
            return;

        $users = connection()->getChannel($channel)->setUsers($data[3]);


        foreach($users as $user)
        {
            if(!database()->has('users', 'nick', $user))
            {
                send("WHO", $user);
            }
        }
    }
}