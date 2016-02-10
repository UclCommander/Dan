<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\Traits\Parser;

class Packet353 implements PacketContract
{
    use Parser;

    public function handle(Connection $connection, array $from, array $data)
    {
        $channel = $data[2];

        if (!$connection->inChannel($channel)) {
            return;
        }

        $users = $this->parse353($data[3]);

        $channel = $connection->getChannel($channel);

        foreach ($users as $user => $prefix) {
            $channel->addUser($user, $prefix);
        }
    }
}