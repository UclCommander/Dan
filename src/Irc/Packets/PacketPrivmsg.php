<?php

namespace Dan\Irc\Packets;

use Dan\Contracts\PacketContract;
use Dan\Events\Traits\EventTrigger;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Irc\Traits\CTCP;

class PacketPrivmsg implements PacketContract
{
    use EventTrigger, CTCP;

    /**
     * Handles the PRIVMSG Packet.
     *
     * @param \Dan\Irc\Connection $connection
     * @param array               $from
     * @param array               $data
     *
     * @throws \Exception
     */
    public function handle(Connection $connection, array $from, array $data)
    {
        // TODO: TEMP
        // -------------------------------------------------------------------------------------------------------------
        if ($data[1] === '.memory') {
            $memory = convert(memory_get_usage());
            $peak = convert(memory_get_peak_usage());
            $connection->message($data[0], "[ <cyan>Memory Usage:</cyan> <yellow>{$memory}</yellow> | <cyan>Peak Usage:</cyan> <yellow>{$peak}</yellow> ]");

            return;
        }
        // -------------------------------------------------------------------------------------------------------------

        $user = new User($connection, $from);
        $message = $data[1];

        if ($this->hasCTCP($message)) {
            if (!empty(($return = $this->handleCTCP($connection, $user, $message)))) {
                $connection->notice($user, $this->prepareCTCP(...$return));
            }

            return;
        }

        if ($connection->isChannel($data[0])) {
            if (!$connection->inChannel($data[0])) {
                return;
            }

            $channel = $connection->getChannel($data[0]);

            $this->triggerEvent('irc.message.public', [
                'connection' => $connection,
                'channel'    => $channel,
                'user'       => $user,
                'message'    => $message,
            ]);
        } else {
            $this->triggerEvent('irc.message.private', [
                'connection' => $connection,
                'user'       => $user,
                'message'    => $message,
            ]);
        }
    }

    /**
     * Handles a CTCP event.
     *
     * @param \Dan\Irc\Connection    $connection
     * @param \Dan\Irc\Location\User $user
     * @param $message
     *
     * @return array
     */
    protected function handleCTCP(Connection $connection, User $user, $message) : array
    {
        $ctcp = $this->parseCTCP($message);

        $normalized = ucfirst(strtolower($ctcp['command']));

        if (method_exists($this, 'ctcp'.$normalized)) {
            return [$ctcp['command'], $this->{'ctcp'.$normalized}()];
        }

        $response = $this->triggerEvent('irc.ctcp.'.strtolower($ctcp['command']), [
            'connection' => $connection,
            'user'       => $user,
            'message'    => $ctcp['message'],
        ]);

        if (!is_string($response)) {
            return [];
        }

        return [$ctcp['command'], $response];
    }
}
