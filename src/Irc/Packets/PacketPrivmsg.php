<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Dan\Irc\Traits\CTCP;
use Dan\Irc\Traits\Ignore;

class PacketPrivmsg extends Packet
{
    use EventTrigger, CTCP, Ignore;

    /**
     * Handles the PRIVMSG Packet.
     *
     * @param array               $from
     * @param array               $data
     */
    public function handle(array $from, array $data)
    {
        $user = new User($this->connection, $from);
        $message = $data[1] ?? null;

        logger()->logNetworkChannelItem($this->connection->getName(), $data[0], $message, $from[0]);

        console()->message("[<magenta>{$this->connection->getName()}</magenta>][<cyan>{$data[0]}</cyan>][<yellow>{$from[0]}</yellow>] {$message}");

        if ($this->isIgnored($user)) {
            return;
        }

        if ($this->connection->isChannel($data[0])) {
            if (!$this->connection->inChannel($data[0])) {
                return;
            }

            $channel = $this->connection->getChannel($data[0]);

            if ($this->ctcp($user, $message, $channel)) {
                return;
            }

            $this->triggerEvent('irc.message.public', [
                'connection' => $this->connection,
                'channel'    => $channel,
                'user'       => $channel->getUser($user),
                'message'    => $message,
            ]);
        } else {
            if ($this->ctcp($user, $message)) {
                return;
            }

            $this->triggerEvent('irc.message.private', [
                'connection' => $this->connection,
                'user'       => $user,
                'message'    => $message,
            ]);
        }
    }

    /**
     * @param \Dan\Irc\Location\User $user
     * @param $message
     * @param \Dan\Irc\Location\Channel|null $channel
     *
     * @return bool
     */
    protected function ctcp(User $user, $message, Channel $channel = null)
    {
        if ($this->hasCTCP($message)) {
            if (!empty(($return = $this->handleCTCP($user, $message, $channel)))) {
                $this->connection->notice($user, $this->prepareCTCP(...$return));
            }

            return true;
        }

        return false;
    }

    /**
     * Handles a CTCP event.
     *
     * @param \Dan\Irc\Location\User $user
     * @param $message
     * @param \Dan\Irc\Location\Channel $channel
     *
     * @return array
     */
    protected function handleCTCP(User $user, $message, Channel $channel = null) : array
    {
        $ctcp = $this->parseCTCP($message);

        $response = $this->triggerEvent('irc.ctcp.'.strtolower($ctcp['command'].'.'.($channel ? 'public' : 'private')), [
            'connection' => $this->connection,
            'user'       => $user,
            'channel'    => $channel,
            'message'    => $ctcp['message'],
        ]);

        if (is_string($response)) {
            return [$ctcp['command'], $response];
        }

        $normalized = ucfirst(strtolower($ctcp['command']));

        if (method_exists($this, 'ctcp'.$normalized)) {
            return [$ctcp['command'], $this->{'ctcp'.$normalized}()];
        }

        return [];
    }
}
