<?php namespace Dan\Irc\Packets; 


use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;
use Dan\Irc\PacketInfo;

class Packet005 implements PacketContract {

    public function handle(Connection &$connection, PacketInfo $packetInfo)
    {
        $command = $packetInfo->get('command');

        // Kill off the fluff
        array_shift($command);
        array_pop($command);

        foreach($command as $cmd)
        {
            $support = explode('=', $cmd, 2);


            switch($support[0])
            {
                case 'CMDS':
                    $connection->supported->put($support[0], explode(',', $support[1]));
                    break;

                case 'PREFIX':
                    $matches = [];

                    preg_match("/\(([a-z]+)\)(.*)/", $support[1], $matches);

                    $keys   = str_split($matches[1]);
                    $values = str_split($matches[2]);

                    $connection->supported->put($support[0], array_combine($keys, $values));
                    break;

                case 'STATUSMSG':
                    $connection->supported->put($support[0], str_split($support[1]));
                    break;

                default:
                    $connection->supported->put($support[0], end($support));
            }
        }
    }
}