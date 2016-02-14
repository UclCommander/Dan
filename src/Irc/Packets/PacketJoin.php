<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Location\User;

class PacketJoin extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if ($from[0] != $this->connection->user->nick) {
            $channel = $this->connection->getChannel($data[0]);
            $channel->addUser($from[0]);
        } else {
            $this->connection->addChannel($data[0]);
            $this->connection->send('MODE', $data[0]);
        }

        $this->triggerEvent('irc.join', [
            'user'          => new User($this->connection, ...$from),
            'channel'       => $this->connection->getChannel($data[0]),
            'connection'    => $this->connection,
        ]);

        if (!config('dan.debug')) {
            console()->message("[<magenta>{$this->connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>joined {$data[0]}</cyan>");
        }
    }
}
