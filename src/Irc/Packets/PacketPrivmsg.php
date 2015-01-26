<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Dan;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketPrivmsg implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        /** @var \Dan\Irc\Location\User */
        $user       = $packetInfo->get('user');
        $command    = $packetInfo->get('command');

        if(strpos($command[1], "\001") !== false)
        {
            $ctcp = explode(' ', trim($command[1], " \t\n\r\0\x0B\001"), 2);
            $send = Event::fire('irc.packets.ctcp', new EventArgs(['type' => $ctcp[0], 'args' => $ctcp[1]]), true);

            if($send !== null)
            {
                $connection->send("PRIVMSG", $user, "\001{$ctcp[0]} {$send}\001");
                return;
            }

            if($ctcp[0] == 'VERSION')
            {
                $connection->send("NOTICE", $user, "\001VERSION Dan the PHP Bot  v" . Dan::VERSION . " - http://derpy.me/dan3 - PHP " . phpversion() . " \001");
            }

            if($ctcp[0] == 'TIME')
            {
                $connection->send("NOTICE", $user, "\001TIME " . date('r') . "\001");
            }

            if($ctcp[0] == 'PING')
            {
                $connection->send("NOTICE", $user, "\001PING " . time() . "\001");
            }

            return;
        }

        if($command[0] == $connection->user->getNick())
        {
            Event::fire('irc.packets.message.private', new EventArgs($packetInfo));
            return;
        }

        Event::fire('irc.packets.message.public', new EventArgs([
            'channel'   => $connection->getChannel($command[0]),
            'message'   => $command[1],
            'user'      => $user,
        ]));
    }
}