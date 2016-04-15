<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Location\User;

class PacketPart extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->triggerEvent('irc.part', [
            'user'          => new User($this->connection, ...$from),
            'channel'       => $this->connection->getChannel($data[0]),
            'connection'    => $this->connection,
        ]);

        if ($from[0] != $this->connection->user->nick) {
            $channel = $this->connection->getChannel($data[0]);
            $channel->removeUser($from[0]);
        } else {
            $this->connection->removeChannel($data[0]);
        }

        logger()->logNetworkChannelItem($this->connection->getName(), $data[0], "{$from[0]} left the channel");

        if (!config('dan.debug')) {
            console()->message("[<magenta>{$this->connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>left {$data[0]}</cyan>");
        }
    }
}
