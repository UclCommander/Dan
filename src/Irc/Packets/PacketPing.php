<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Connection;

class PacketPing implements PacketContract
{
    use EventTrigger;

    public function handle(Connection $connection, array $from, array $data)
    {
        $connection->send('PONG', ...$data);

        $this->triggerEvent('irc.ping');
    }
}
