<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet396 implements PacketContract {


    public function handle($from, $data)
    {
        if($data[0] == config('irc.user.nick'))
        {
            database()->update('users', ['nick' => $data[0]], [
                'host'  => $data[1]
            ]);
        }
    }
}