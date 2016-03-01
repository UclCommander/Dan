<?php namespace Dan\Irc\Packets;


use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class PacketNick extends Packet
{
    use EventTrigger;

    /**
     * @param array $from
     * @param array $data
     */
    public function handle(array $from, array $data)
    {
        $user = new User($this->connection, $from);
        $nick = $data[0];

        $this->triggerEvent('irc.packets.nick', [
            'user'  => $user,
            'nick'  => $data[0],
        ]);

        $this->connection->database('users')->insertOrUpdate(['nick', $user->nick], [
            'nick'   => $nick,
            'user'   => $user->user,
            'host'   => $user->host,
        ]);

        foreach ($this->connection->channels as $channel) {
            /** @var Channel $channel */
            if ($channel->hasUser($user) != null) {
                $channel->addUser($nick);
                $channel->getUser($nick)->setRawModes($channel->getUser($user)->modes());
                $channel->removeUser($user);
            }
        }
    }
}