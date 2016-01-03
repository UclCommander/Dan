<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet352 implements PacketContract
{
    /**
     * @param \Dan\Irc\Connection $connection
     * @param array               $from
     * @param array               $data
     *
     * @throws \Exception
     */
    public function handle(Connection $connection, array $from, array $data)
    {
        database()->table('users')->insertOrUpdate(['nick', $data[5]], [
            'nick' => $data[5],
            'user' => $data[2],
            'host' => $data[3],
        ]);
    }
}
