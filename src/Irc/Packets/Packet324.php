<?php namespace Dan\Irc\Packets;


use Dan\Contracts\PacketContract;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Connection;
use Dan\Irc\Traits\Parser;

class Packet324 implements PacketContract
{
    use EventTrigger, Parser;

    public function handle(Connection $connection, array $from, array $data)
    {
        if ($data[0] == $connection->user->nick) {
            array_shift($data);
            (new PacketMode())->handle($connection, $from, $data);
        }
    }
}