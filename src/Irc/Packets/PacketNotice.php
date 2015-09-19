<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketNotice implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        if($data[0] == $connection->user->nick())
        {
            $user       = user($from);
            $message    = $data[1];

            event('irc.packets.notice.private', [
                'connection'    => $connection,
                'user'          => $user,
                'message'       => $message
            ]);
        }
    }
}