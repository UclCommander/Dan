<?php namespace Dan\Irc\Packets; 

use Dan\Contracts\PacketContract;
use Dan\Irc\Connection;

class Packet005 implements PacketContract {

    public function handle(Connection $connection, array $from, array $data)
    {
        $data = array_slice($data, 1, -1);

        foreach($data as $support)
        {
            if(strpos($support, '=') === false)
            {
                $connection->support->put($support, true);
                continue;
            }

            list($name, $value) = explode('=', $support);

            $connection->support->put($name, $value);
        }
    }
}