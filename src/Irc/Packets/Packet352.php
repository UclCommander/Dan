<?php

namespace Dan\Irc\Packets;

class Packet352 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $this->connection->database('users')->insertOrUpdate(['nick', $data[5]], [
            'nick' => $data[5],
            'user' => $data[2],
            'host' => $data[3],
        ]);

        if ($data[5] == $this->connection->user->nick) {
            $this->connection->user->setHost($data[3]);
        }
    }
}
