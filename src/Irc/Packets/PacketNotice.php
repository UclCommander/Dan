<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketNotice extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if (!config('dan.debug')) {
            console("[<magenta>{$this->connection->getName()}</magenta>][<cyan>{$data[0]}</cyan>][<yellow>".($from[0] ?? null)."</yellow>] ** {$data[1]}");
        }

        if ($data[0] == $this->connection->user->nick) {
            $user = $this->makeUser($from);
            $message = $data[1];
            
            $this->triggerEvent('irc.notice.private', [
                'connection'    => $this->connection,
                'user'          => $user,
                'message'       => $message,
            ]);
        }
    }
}
