<?php

namespace Dan\Irc\Traits;

use Dan\Core\Dan;

trait CTCP
{
    /**
     * Checks to see if this is a CTCP packet.
     *
     * @param $message
     *
     * @return bool
     */
    public function hasCTCP($message) : bool
    {
        return strpos($message, "\001") === 0;
    }

    /**
     * Parses the CTCP string.
     *
     * @param $message
     *
     * @return array
     */
    public function parseCTCP($message) : array
    {
        $data = explode(' ', trim($message, " \t\n\r\0\x0B\001"), 2);

        return ['command' => $data[0], 'message' => $data[1] ?? null];
    }

    /**
     * Prepares a CTCP string.
     *
     * @param $message
     *
     * @return string
     */
    public function prepareCTCP($packet, $message) : string
    {
        return "\001{$packet} {$message}\001";
    }

    /**
     * Gets the CTCP VERSION response.
     *
     * @return string
     */
    public function ctcpVersion()
    {
        return 'Dan the PHP Bot v'.Dan::VERSION.' by UclCommander. Running on PHP '.phpversion();
    }

    /**
     * Gets the CTCP TIME response.
     *
     * @return bool|string
     */
    public function ctcpTime()
    {
        return date('r');
    }

    /**
     * Gets the CTCP PING response.
     *
     * @return int
     */
    public function ctcpPing()
    {
        return time();
    }
}
