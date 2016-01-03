<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketMode implements PacketContract
{
    public function handle(Connection $connection, array $from, array $data)
    {
        if ($data[0] == $connection->user->nick()) {
            $connection->user->setMode($data[1]);

            return;
        }

        $user = user($from);
        $channel = $data[0];
        $modes = $data[1];

        if (!connection()->inChannel($channel)) {
            return;
        }

        $channel = connection()->getChannel($channel);

        if (count($data) == 2) {
            event('irc.packets.mode.channel', [
                'channel'   => $channel,
                'modes'     => $modes,
                'user'      => $user,
            ]);

            $channel->setMode($modes);

            return;
        }

        array_shift($data);
        array_shift($data);

        $users = $data;
        $final = [];
        $index = 0;
        $add = true;

        foreach (str_split($modes) as $mode) {
            if ($mode == '+' || $mode == '-') {
                $add = ($mode == '+');
                continue;
            }

            $final[] = [$users[$index], ($add ? '+' : '-').$mode];
            $index++;
        }

        $channel->updateUserModes($final);

        event('irc.packets.mode.user', [
            'user'      => $user,
            'channel'   => $channel,
            'modes'     => $final,
        ]);
    }
}
