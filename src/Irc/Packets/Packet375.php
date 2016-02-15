<?php

namespace Dan\Irc\Packets;

class Packet375 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if (!config('dan.debug') && $this->connection->config->get('show_motd')) {
            console()->message(end($data));
        }
    }
}
