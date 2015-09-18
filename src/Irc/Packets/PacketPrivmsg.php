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
            $channel    = $connection->getChannel($data[0]);
            $user       = $channel->getUser(user($from));
            $message    = $data[1];

            $hookData = [
                'connection'    => $connection,
                'user'          => $user,
                'channel'       => $channel,
                'message'       => $message
            ];

            if(strpos($message, "\001") === 0)
            {
                $ctcp = explode(' ', trim($message, "\001"), 2);

                var_dump($ctcp, $data, str_split($ctcp[0]), ($ctcp[0] == "ACTION"));

                if($ctcp[0] == 'ACTION')
                {
                    var_dump($ctcp[0]);
                    $hookData['message'] = $ctcp[1];
                    event('irc.packets.action.public', $hookData);
                    return;
                }

                return;
            }

            if(event('irc.packets.message.public', $hookData) === false)
                return;

            $info   = database()->table('channels')->where('name', $channel->getLocation())->first()->get('info');
            $except = isset($info['disabled_hooks']) ? $info['disabled_hooks'] : [];

            if(HookManager::data($hookData)->except($except)->call('regex'))
                return;

            if(HookManager::data($hookData)->except($except)->call('command'))
                return;
        }
    }
}