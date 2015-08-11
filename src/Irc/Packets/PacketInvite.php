<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Core\Dan;

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

        $inviter = user($data[0]);

        if($inviter == null)
            return;

        if(!config('irc.join_on_invite') && !Dan::isAdminOrOwner($inviter))
            return;

        controlLog("{$data[0]} invited me to {$data[1]}");

        connection()->joinChannel($data[1]);
    }
}