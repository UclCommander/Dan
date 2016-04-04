<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketTopic extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if (!$this->connection->inChannel($data[0])) {
            return;
        }

        $channel = $this->connection->getChannel($data[0]);

        $this->triggerEvent('irc.topic', [
            'user'      => $this->makeUser($from),
            'channel'   => $channel,
            'topic'     => $data[1],
        ]);
    }
}
