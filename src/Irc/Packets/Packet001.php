<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet001 implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        if (!config('dan.debug')) {
            console()->message("[<magenta>{$from[0]}</magenta>] {$data[1]}");
        }
    }
}
