<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;

class PacketInvite extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $inviter = $this->makeUser($from);

        $this->triggerEvent('irc.invite', [
            'user'      => $inviter,
            'who'       => $data[0],
            'channel'   => $data[1],
        ]);

        if ($data[0] != $this->connection->user->nick) {
            return;
        }

        if (!$this->connection->config->get('join_on_invite', false) && !$this->connection->isAdminOrOwner($inviter)) {
            return;
        }

        if (!config('dan.debug')) {
            console("[<magenta>{$this->connection->getName()}</magenta>] <yellow>{$inviter->nick}</yellow> invited me to <cyan>{$data[1]}</cyan>");
        }

        $this->connection->joinChannel($data[1]);
    }
}
