<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet376 implements PacketContract {


    public function handle($from, $data)
    {
        if(config('irc.user.pass') != '')
            raw(sprintf(config('irc.nickserv_auth_command'), config('irc.user.pass')));

        $channels = config('irc.channels');

        foreach($channels as $channel)
            connection()->joinChannel($channel);
    }
}