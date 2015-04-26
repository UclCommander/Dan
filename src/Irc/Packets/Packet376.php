<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Console\Console;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class Packet376 implements PacketContract {


    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        if($connection->config->get('show_motd') === true)
            Console::info($packetInfo->get('command')[1]);

        // If it's an unreal server, send +B for bots
        foreach($connection->numeric->get('004') as $d)
            if (strpos($d, 'Unreal3') === 0)
                $connection->sendRaw("MODE {$connection->config->get('nickname')} +B");

        if(!empty($connection->config->get('password')))
        {
            Console::info('Authenticating with NickServ');

            $connection->sendRaw(sprintf($connection->config->get('nickserv_auth_command'), $connection->config->get('password')));

            Event::subscribe('irc.packet.mode', function(EventArgs $eventArgs) use($connection)
            {
                $data = $eventArgs->get('data');

                if($data[0] == $connection->config->get('nickname'))
                    if ($data[1] == '+r')
                        return $this->joinChannels($connection);

                return null;

            }, EventPriority::Critical);
        }

        $this->joinChannels($connection);
    }

    /**
     * Joins channels.
     *
     * @param \Dan\Irc\Connection $connection
     * @return string
     */
    public function joinChannels(Connection $connection)
    {
        $connection->sendRaw("WHO {$connection->config->get('nickname')}");

        foreach($connection->config->get('channels') as $autoJoinChannel)
        {
            $password = explode(':', $autoJoinChannel);

            $connection->joinChannel($password[0], (isset($password[1]) ? $password[1] : null));
        }


        return Event::Destroy;
    }
}