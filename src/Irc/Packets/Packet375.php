<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet375 implements PacketContract {


    public function handle($from, $data)
    {
        if(config('irc.show_motd'))
            console(end($data));
    }
}