<?php namespace Dan\Irc\Packets; 


use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Irc\Connection;
use Dan\Irc\Packet;
use Dan\Irc\PacketInfo;

class Packet376 extends Packet {


    public function handlePacket(Connection &$connection, PacketInfo $packetInfo)
    {
        //If it's an unreal server, send +B for bots
        foreach($connection->numeric->get('004') as $d)
            if(strpos($d, 'Unreal3') === 0)
                $connection->sendRaw("MODE {$connection->config->get('nickname')} +B");

        if(!empty(Config::get('irc.password')))
        {
            Console::text('Sending NickServ password..')->info()->debug()->push();
            $connection->sendRaw(sprintf(Config::get('irc.nickserv_auth_command'), Config::get('irc.password')));

            Event::subscribeOnce('irc.packet.mode', function(EventArgs $eventArgs) use($connection)
            {
                if($eventArgs['data'][0] == $connection->config->get('nickname'))
                    if($eventArgs['data'][1] == '+r')
                        $this->joinChannels($connection);

            }, EventPriority::Critical);

            return;
        }

        $this->joinChannels($connection);
    }

    public function joinChannels(Connection $connection)
    {
        foreach($connection->config['channels'] as $autoJoinChannel)
        {
            $password = explode(':', $autoJoinChannel);
            $connection->joinChannel($password[0], (isset($password[1]) ? $password[1] : null));
        }
    }
}