<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketPing extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->connection->send('PONG', ...$data);

        $this->triggerEvent('irc.ping');
    }
}
