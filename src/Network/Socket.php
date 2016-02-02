<?php

namespace Dan\Network;

use Dan\Network\Exceptions\BrokenPipeException;

class Socket
{
    /** @var resource $socket  */
    protected $socket;

    /**
     * Gets the socket resource.
     *
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Connects to the given server.
     *
     * @param string $server
     * @param int    $port
     *
     * @throws \Exception
     */
    public function connect($server, $port)
    {
        $this->socket = @fsockopen($server, $port, $errno, $errstr);

        if ($this->socket == false) {
            throw new \Exception($errstr);
        }
    }

    /**
     * Writes to the socket.
     *
     * @param $line
     *
     * @throws \Dan\Network\Exceptions\BrokenPipeException
     */
    public function write($line)
    {
        if (fwrite($this->socket, $line) === false) {
            throw new BrokenPipeException();
        }
    }

    /**
     * Reads from the socket.
     *
     * @return array
     */
    public function read()
    {
        $lines = fread($this->socket, (1024 * 30));

        if ($lines === false) {
            console()->warn('Failed reading from socket.');
        }

        return explode("\n", $lines);
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        if (!is_resource($this->socket)) {
            return false;
        }

        return fclose($this->socket);
    }
}
