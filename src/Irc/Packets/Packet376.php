<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class Packet376 implements PacketContract {


    public function handle($from, $data)
    {
        foreach(config('irc.autorun_commands') as $command)
            raw(str_replace(['{NICK}'], [config('irc.user.nick')], $command));

        if(config('irc.user.pass') != '')
            raw(sprintf(config('irc.nickserv_auth_command'), config('irc.user.pass')));

        $channels   = config('irc.channels');

        foreach($channels as $channel)
        {
            $data = explode(':', $channel);

            connection()->joinChannel($data[0], (isset($data[1]) ? $data[1] : null));
        }
    }
}