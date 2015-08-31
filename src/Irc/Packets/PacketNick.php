<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class PacketNick implements PacketContract {


    public function handle(Connection $connection, array $from, array $data)
    {
        $user = user($from);
        $nick = $data[0];

        event('irc.packets.nick', [
            'user'  => $from,
            'nick'  => $data[0],
        ]);

        database()->table('users')->insertOrUpdate(['nick', $user->nick()], [
            'nick'   => $nick,
            'user'   => $user->user(),
            'host'   => $user->host(),
        ]);

        foreach($connection->channels as $channel)
            if($channel->hasUser($user) != null)
                $channel->renameUser($user, $nick);
    }
}