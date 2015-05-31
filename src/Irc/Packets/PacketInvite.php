<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class PacketInvite implements PacketContract {


    public function handle($from, $data)
    {
        event('irc.packets.invite', [
            'user'      => user($from),
            'who'       => $data[0],
            'channel'   => $data[1]
        ]);

        if($data[0] != connection()->user()->nick())
            return;

        if(!config('irc.join_on_invite'))
            return;


        connection()->joinChannel($data[1]);
    }
}