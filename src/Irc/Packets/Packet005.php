<?php

namespace Dan\Irc\Packets;

class Packet005 extends Packet
{
    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $data = array_slice($data, 1, -1);

        foreach ($data as $support) {
            if (strpos($support, '=') === false) {
                $this->connection->supported->put($support, true);
                continue;
            }

            list($name, $value) = explode('=', $support);

            $this->connection->supported->put($name, $value);
        }
    }
}
