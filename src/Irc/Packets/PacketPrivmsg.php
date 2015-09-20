<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Core\Dan;
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
            $message    = $data[1] ?? null;

            if($user == null)
                return;

            $hookData = [
                'connection'    => $connection,
                'user'          => $user,
                'channel'       => $channel,
                'message'       => $message
            ];

            if(strpos($message, "\001") === 0)
            {
                $ctcp = explode(' ', trim($message, " \t\n\r\0\x0B\001"), 2);

                if($ctcp[0] == "ACTION")
                {
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
        else
        {
            $user       = user($from);
            $message    = $data[1];

            $hookData = [
                'connection'    => $connection,
                'user'          => $user,
                'message'       => $message
            ];

            if(strpos($message, "\001") === 0)
            {
                $ctcp = explode(' ', trim($message, " \t\n\r\0\x0B\001"), 2);

                if($ctcp[0] == "ACTION")
                {
                    $hookData['message'] = $ctcp[1];
                    event('irc.packets.action.private', $hookData);
                    return;
                }

                $send = '';

                if($ctcp[0] == "VERSION")
                {
                    $v = Dan::getCurrentGitVersion();
                    $send = "Dan the PHP Bot v" . Dan::VERSION . "({$v['id']}) by UclCommander, running on PHP " . phpversion() . " - http://skycld.co/dan";
                }

                if($ctcp[0] == "TIME")
                    $send = date('r');

                if($ctcp[0] == "PING")
                    $send = time();

                if(!empty($send))
                    $connection->notice($user, "\001{$ctcp[0]} {$send}\001");

                return;
            }

            if(event('irc.packets.message.private', $hookData) === false)
                return;
        }
    }
}