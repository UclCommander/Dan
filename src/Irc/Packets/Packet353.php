<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Irc\PacketInfo;

class Packet353 implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        /** @var \Dan\Irc\Location\Channel $channel */
        $channel = $connection->getChannel($packetInfo->get('command')[2]);

        if($channel == null)
            return;

        $userList = $packetInfo->get('command')[3];

        $users = explode(' ', $userList);

        foreach($users as $user)
        {
            $rank = '';
            $nick = $user;

            if(!ctype_alnum(substr($user, 0, 1)))
            {
                $rank = substr($user, 0, 1);
                $nick = substr($user, 1);
            }

            $obj = new User($nick);
            $obj->setPrefix($rank);

            $channel->addUser($obj);
        }
    }
}