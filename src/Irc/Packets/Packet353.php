<?php

namespace Dan\Irc\Packets;

use Dan\Irc\Traits\Parser;

class Packet353 extends Packet
{
    use Parser;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $channel = $data[2];

        if (!$this->connection->inChannel($channel)) {
            return;
        }

        $users = $this->parse353($data[3]);

        $channel = $this->connection->getChannel($channel);

        foreach ($users as $user => $prefix) {
            $channel->addUser($user, $prefix);
        }
    }
}
