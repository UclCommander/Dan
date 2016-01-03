<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketJoin implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        if ($from[0] != $connection->user->nick()) {
            $channel = $connection->getChannel($data[0]);

            $channel->setUsers($from[0]);
        } else {
            $connection->addChannel($data[0]);
        }

        event('irc.packets.join', [
            'user'          => user($from),
            'channel'       => $connection->getChannel($data[0]),
            'connection'    => $connection,
        ]);

        if (!DEBUG) {
            console("[<magenta>{$connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>joined {$data[0]}</cyan>");
        }
    }
}
