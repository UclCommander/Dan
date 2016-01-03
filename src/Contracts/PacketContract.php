<?php

namespace Dan\Contracts;

use Dan\Irc\Connection;

interface PacketContract
{
    public function handle(Connection $connection, array $from, array $data);
}
