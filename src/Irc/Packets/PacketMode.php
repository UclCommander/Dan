<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Traits\Parser;

class PacketMode extends Packet
{
    use EventTrigger, Parser;

    /**
     * @param array $from
     * @param array $data
     *
     * @throws \Exception
     */
    public function handle(array $from, array $data)
    {
        $location = $data[0];
        $modes = $data[1];

        array_shift($data);
        array_shift($data);

        $modes = $this->parseModes($modes, $data);

        if ($this->connection->isChannel($location)) {
            if (!$this->connection->inChannel($location)) {
                return;
            }

            $channel = $this->connection->getChannel($location);

            foreach ($modes as $mode) {
                if (!is_null($mode['option'])) {
                    if ($channel->hasUser($mode['option'])) {
                        $channel->setUserMode($mode['option'], $mode['mode']);
                        continue;
                    }
                }

                $channel->setMode($mode['mode'], $mode['option']);
            }

            return;
        }

        if ($location == $this->connection->user->nick) {
            $this->connection->user->setModes($modes);

            $this->triggerEvent('irc.bot.mode', [
                'connection' => $this->connection,
                'user'       => $this->connection->user,
                'mode'       => $modes,
            ]);

            return;
        }
    }
}
