<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketKick implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        $channel = $connection->getChannel($data[0]);

        event('irc.packets.kick', [
            'user'          => $channel->getUser(user($from)),
            'kicked'        => user($data[1]),
            'message'       => isset($data[2]) ? $data[2] : null,
            'channel'       => $channel,
            'connection'    => $connection,
        ]);

        if ($data[1] != $connection->user->nick()) {
            $channel->removeUser($data[1]);
        } else {
            $connection->removeChannel($data[0]);
        }

        if (!DEBUG) {
            console("[<magenta>{$connection->getName()}</magenta>] <yellow>{$data[0]}</yellow> <cyan>was kicked from {$data[0]} by <yellow>{$from[0]}</yellow></cyan>");
        }
    }
}
