<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class PacketNotice implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $command = $packetInfo->get('command');

        // Ignore AUTH Notices
        if($command[0] == 'AUTH')
        {
            Console::text($command[1])->info()->push();
            return;
        }

        Event::fire('irc.packet.notice', new EventArgs($packetInfo));
    }
}