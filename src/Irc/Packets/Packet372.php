<?php

namespace Dan\Irc\Packets;

class Packet372 extends Packet
{
    public function handle(array $from, array $data)
    {
        if (!config('dan.debug') && $this->connection->config->get('show_motd')) {
            console()->message(end($data));
        }
    }
}
