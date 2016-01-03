<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Core\Dan;
use Dan\Irc\Connection;

class PacketInvite implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        $inviter = user($from);

        event('irc.packets.invite', [
            'user'      => $inviter,
            'who'       => $data[0],
            'channel'   => $data[1],
        ]);

        if ($data[0] != $connection->user->nick()) {
            return;
        }

        if ($inviter == null) {
            return;
        }

        if (!config('irc.join_on_invite') && !Dan::isAdminOrOwner($inviter)) {
            return;
        }

        controlLog("{$inviter->nick()} invited me to {$data[1]}");

        $connection->joinChannel($data[1]);
    }
}
