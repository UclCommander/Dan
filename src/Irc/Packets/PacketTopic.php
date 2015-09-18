<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketTopic implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        if(!$connection->inChannel($data[0]))
            return;

        $channel = $connection->getChannel($data[0]);

        event('irc.packets.title', [
            'user'      => user($from),
            'channel'   => $channel,
            'topic'     => $data[1],
        ]);
    }
}

