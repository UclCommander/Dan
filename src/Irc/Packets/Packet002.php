<?php

namespace Dan\Irc\Packets;

class Packet002 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if (!config('dan.debug')) {
            console()->message("[<magenta>{$from[0]}</magenta>] {$data[1]}");
        }
    }
}
