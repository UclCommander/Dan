<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet352 implements PacketContract {


    public function handle($from, $data)
    {
        database()->table('users')->insertOrUpdate(['nick', $data[5]], [
            'nick' => $data[5],
            'user' => $data[2],
            'host' => $data[3],
        ]);
    }
}