<?php

namespace Dan\Irc\Packets;

use Dan\Events\Traits\EventTrigger;
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

        console()->message("[<magenta>{$this->connection->getName()}</magenta>][<cyan>{$data[0]}</cyan>][<yellow>{$from[0]}</yellow>] {$message}");

        if ($this->isIgnored($user)) {
            return;
        }

        if ($this->hasCTCP($message)) {
            if (!empty(($return = $this->handleCTCP($user, $message)))) {
                $this->connection->notice($user, $this->prepareCTCP(...$return));
            }

            return;
        }

        if ($this->connection->isChannel($data[0])) {
            if (!$this->connection->inChannel($data[0])) {
                return;
            }

            $channel = $this->connection->getChannel($data[0]);

            $this->triggerEvent('irc.message.public', [
                'connection' => $this->connection,
                'channel'    => $channel,
                'user'       => $channel->getUser($user),
                'message'    => $message,
            ]);
        } else {
            $this->triggerEvent('irc.message.private', [
                'connection' => $this->connection,
                'user'       => $user,
                'message'    => $message,
            ]);
        }
    }

    /**
     * Handles a CTCP event.
     *
     * @param \Dan\Irc\Location\User $user
     * @param $message
     *
     * @return array
     */
    protected function handleCTCP(User $user, $message) : array
    {
        $ctcp = $this->parseCTCP($message);

        $normalized = ucfirst(strtolower($ctcp['command']));

        if (method_exists($this, 'ctcp'.$normalized)) {
            return [$ctcp['command'], $this->{'ctcp'.$normalized}()];
        }

        $response = $this->triggerEvent('irc.ctcp.'.strtolower($ctcp['command']), [
            'connection' => $this->connection,
            'user'       => $user,
            'message'    => $ctcp['message'],
        ]);

        if (!is_string($response)) {
            return [];
        }

        return [$ctcp['command'], $response];
    }
}
