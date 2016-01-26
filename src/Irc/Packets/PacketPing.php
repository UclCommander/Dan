<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Setup\Update;

class PacketPing implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        $connection->send('PONG', $data[0]);

        event('irc.packets.ping');

        debug("Saving database information");

        foreach ($connection->channels as $channel) {
            $channel->save();
        }

        Update::autoUpdate();
    }
}
