<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Hooks\HookManager;
use Dan\Irc\Connection;

class PacketPrivmsg implements PacketContract {


    public function handle(Connection $connection, array $from, array $data)
    {
        if(!DEBUG)
            console("[<magenta>{$connection->getName()}</magenta>][<yellow>{$from[0]}</yellow>] {$data[1]}");

        if(isChannel($data[0]))
        {
            $channel = $connection->getChannel($data[0]);

            $user = $channel->getUser(user($from));

            $hookData = [
                'connection'    => $connection,
                'user'          => $user,
                'channel'       => $channel,
                'message'       => $data[1]
            ];

            if(event('irc.packets.message.public', $hookData) === false)
                return;

            if(HookManager::callRegexHooks($hookData))
                return;

            if(HookManager::callCommandHooks($hookData))
                return;

            /*if(!$ran)
                $connection->send("PRIVMSG", $data[0], $data[1]);*/
        }
    }
}