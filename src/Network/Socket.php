<?php namespace Dan\Network;

class Socket {

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
     * @param int $port
     * @throws \Exception
     */
    public function connect($server, $port)
    {
        $this->socket = fsockopen($server, $port, $errno, $errstr);

        if($this->socket == false)
            throw new \Exception($errstr);
    }


    /**
     * Writes to the socket.
     *
     * @param $line
     */
    public function write($line)
    {
        fwrite($this->socket, $line);
    }


    /**
     * Reads from the socket.
     *
     * @return array
     */
    public function read()
    {
        $lines = fread($this->socket, 10240);

        if($lines === false)
            critical("Failed reading from socket.", true);

        return explode("\n", $lines);
    }
}