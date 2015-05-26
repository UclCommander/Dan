<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;

class PacketNick implements PacketContract {


    public function handle($from, $data)
    {
        $user = user($from);
        $nick = $data[0];

        event('irc.packets.nick', [
            'user'  => $from,
            'nick'  => $data[0],
        ]);

        database()->insertOrUpdate('users', ['nick' => $user->nick()], [
           'nick'   => $nick,
           'user'   => $user->user(),
           'host'   => $user->host(),
        ]);

        foreach(connection()->channels() as $channel)
            if($channel->hasUser($user) != null)
                $channel->renameUser($user, $nick);
    }
}