<?php namespace Dan\Irc\Packets;


use Dan\Contracts\PacketContract;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Connection;
use Dan\Irc\Traits\Parser;

class PacketMode implements PacketContract
{
    use EventTrigger, Parser;

    public function handle(Connection $connection, array $from, array $data)
    {
        $location = $data[0];
        $modes = $data[1];

        array_shift($data);
        array_shift($data);

        $modes = $this->parseModes($modes, $data);

        if ($connection->isChannel($location)) {
            if (!$connection->inChannel($location)) {
                return;
            }

            $channel = $connection->getChannel($location);

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

        if ($location == $connection->user->nick) {
            $connection->user->setModes($modes);

            $this->triggerEvent('irc.bot.mode', [
                'connection' => $connection,
                'user'       => $connection->user,
                'mode'       => $modes,
            ]);

            return;
        }
    }
}