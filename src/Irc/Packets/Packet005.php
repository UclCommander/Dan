<?php namespace Dan\Irc\Packets; 


use Dan\Irc\Connection;
use Dan\Irc\Packet;
use Dan\Irc\PacketInfo;
use Dan\Irc\Support;

class Packet005 extends Packet {


    public function handlePacket(Connection &$connection, PacketInfo $packetInfo)
    {
        $data = $packetInfo->get('data');

        array_shift($data); // remove username from the list
        array_pop($data); //remove "are supported by this server"

        foreach($data as $s)
        {
            $kv = explode('=', $s, 2);

            $value = null;

            switch($kv[0])
            {
                case 'CMDS':
                    $value = explode(',',$kv[1]);
                    break;

                case 'CHANTYPES':
                    $value = str_split($kv[1]);
                    break;

                case 'PREFIX':
                    $matches = [];

                    if(preg_match("/\(([a-z]+)\)(.*)/", $kv[1], $matches))
                    {
                        array_shift($matches);

                        $value = [
                            str_split($matches[0]),
                            str_split($matches[1]),
                        ];
                    }

                    break;

                default:
                    $value = count($kv) == 2 ? $kv[1] : null;
            }

            Support::put($kv[0], $value);
        }
    }
}