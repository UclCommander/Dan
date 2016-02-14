<?php

namespace Dan\Irc\Packets;

class PacketNotice extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        // var_dump($from, $data);
    }
}
