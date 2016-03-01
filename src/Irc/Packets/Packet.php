<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;

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

    /**
     * Makes a user because I'm lazy.
     *
     * @param $data
     *
     * @return \Dan\Irc\Location\User
     */
    protected function makeUser($data) : User
    {
        return new User($this->connection, $data);
    }
}
