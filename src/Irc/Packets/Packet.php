<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

abstract class Packet implements PacketContract
{
    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * Packet constructor.
     *
     * @param \Dan\Irc\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
