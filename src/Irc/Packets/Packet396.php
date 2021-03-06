<?php

namespace Dan\Irc\Packets;

class Packet396 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     *
     * @throws \Exception
     */
    public function handle(array $from, array $data)
    {
        if ($data[0] == $this->connection->user->nick) {
            $this->connection->user->setHost($data[1]);

            $this->connection->database('users')->where('nick', $data[0])->update([
                'host'  => $data[1],
            ]);
        }
    }
}
