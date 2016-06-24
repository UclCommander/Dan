<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Traits\Parser;

class Packet333 extends Packet
{
    use EventTrigger;
    use Parser;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if (!$this->connection->inChannel($data[1])) {
            return;
        }

        $channel = $this->connection->getChannel($data[1]);

        $this->triggerEvent('irc.topic.set_by', [
            'connection'    => $this->connection,
            'channel'       => $channel,
            'user'          => $this->makeUser(parseUserString($data[2])),
            'time'          => $data[3],
        ]);
    }
}
