<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;

class PacketJoin implements PacketContract
{
    use EventTrigger;

    public function handle(Connection $connection, array $from, array $data)
    {
        if ($from[0] != $connection->user->nick) {
            $channel = $connection->getChannel($data[0]);
            $channel->addUser($from[0]);
        } else {
            $connection->addChannel($data[0]);
        }

        $this->triggerEvent('irc.join', [
            'user'          => new User($connection, ...$from),
            'channel'       => $connection->getChannel($data[0]),
            'connection'    => $connection,
        ]);

        if (!config('dan.debug')) {
            console()->message("[<magenta>{$connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>joined {$data[0]}</cyan>");
        }
    }
}
