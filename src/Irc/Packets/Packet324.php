<?php namespace Dan\Irc\Packets;

class Packet324 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        if ($data[0] == $this->connection->user->nick) {
            array_shift($data);
            (new PacketMode($this->connection))->handle($from, $data);
        }
    }
}