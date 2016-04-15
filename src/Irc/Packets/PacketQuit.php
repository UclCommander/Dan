<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketQuit extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $user = $this->makeUser($from);

        $this->triggerEvent('irc.quit', [
            'connection'    => $this->connection,
            'user'          => $user,
            'message'       => ($data[0] ?? null),
        ]);

        foreach ($this->connection->channels() as $channel) {
            logger()->logNetworkChannelItem($this->connection->getName(), $channel, 'left the network', $from[0]);

            $channel->removeUser($user);
        }

        if (!config('dan.debug')) {
            console()->message("[<magenta>{$this->connection->getName()}</magenta>] <yellow>{$from[0]}</yellow> <cyan>left the network</cyan>");
        }
    }
}
