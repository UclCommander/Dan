<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketKick extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $channel = $this->connection->getChannel($data[0]);

        $this->triggerEvent('irc.kick', [
            'user'          => $channel->getUser($this->makeUser($from)),
            'kicked'        => $this->makeUser($data[1]),
            'message'       => isset($data[2]) ? $data[2] : null,
            'channel'       => $channel,
            'connection'    => $this->connection,
        ]);

        if ($data[1] != $this->connection->user->nick) {
            $channel->removeUser($data[1]);
        } else {
            $this->connection->removeChannel($data[0]);
        }

        if (!config('dan.debug')) {
            console("[<magenta>{$this->connection->getName()}</magenta>] <yellow>{$data[0]}</yellow> was kicked from <cyan>{$data[0]}</cyan> by <yellow>{$from[0]}</yellow>");
        }
    }
}
