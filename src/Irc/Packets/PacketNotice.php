<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;

class PacketNotice implements PacketContract {


    public function handle($from, $data)
    {
        if(isServer($from))
        {
            event('irc.packets.notice.server', [
                'type'      => $data[0],
                'message'   => $data[1],
            ]);

            return;
        }

        if(isChannel($data[0]))
        {
            if(!connection()->inChannel($data[0]))
                return;

            event('irc.packets.notice.channel', [
                'channel'   => connection()->getChannel($data[0]),
                'user'      => user($from),
                'message'   => $data[1],
            ]);

            return;
        }

        event('irc.packets.notice.user', [
            'user'      => user($from),
            'message'   => $data[1],
        ]);
    }
}