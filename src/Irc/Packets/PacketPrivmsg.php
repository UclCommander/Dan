<?php namespace Dan\Irc\Packets;


use Dan\Core\Dan;
use Dan\Irc\Connection;
use Dan\Irc\PacketInterface;
use Dan\Irc\User;

class PacketPrivmsg implements PacketInterface {


    public function run(Connection &$connection, array $data, User $user)
    {
        if($data[1] == "\001VERSION\001")
        {
            $connection->sendNotice($user->getNick(), "\001VERSION Dan " . Dan::VERSION . " - PHP " . phpversion() . "\001");
            return;
        }
        else if($data[1] == "\001PING\001")
        {
            $connection->sendNotice($user->getNick(), "\001PING " . time() . " \001");
            return;
        }
        else if($data[1] == "\001TIME\001")
        {
            $connection->sendNotice($user->getNick(), "\001TIME " . date("r") . "\001");
            return;
        }

        $connection->sendMessage($data[0], $data[1]);
    }
}
 