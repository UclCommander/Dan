<?php

namespace Dan\Irc\Packets;

class Packet004 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->connection->serverInfo->put('server_name', $data[1]);

        if ($data[0] != 'null') {
            $this->connection->serverInfo->put('server_name', $data[1]);
            $this->connection->serverInfo->put('version', $data[2]);
            $this->connection->serverInfo->put('user_modes', $data[3]);
            $this->connection->serverInfo->put('channel_modes', $data[4]);
        }
    }
}
