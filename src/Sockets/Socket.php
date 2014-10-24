<?php namespace Dan\Sockets;

class Socket
{
    private $socket = null;

    /**
     * Gets the last error number
     *
     * @return int
     */
    public function getLastErrorNum() { return socket_last_error($this->socket); }

    /**
     * Gets the last error with a message
     *
     * @return string
     */
    public function getLastErrorStr() { return socket_strerror($this->getLastErrorNum()); }

    /***
     * Create the socket and initialize it
     *
     * @param int $domain
     * @param int $type
     * @param int $protocol
     */
    public function init($domain = AF_INET, $type = SOCK_RAW, $protocol = SOL_TCP)
    {
        $socket = socket_create($domain, $type, $protocol);

        if($socket !== false)
            $this->socket = $socket;
        else
            $this->throwError();

    }

    /**
     * Connect to the address
     *
     * @param $address
     * @param $port
     * @return bool
     */
    public function connect($address, $port)
    {
        return socket_connect($this->socket, $address, (int)$port);
    }

    /**
     * Closes the socket connection
     */
    public function close()
    {
       socket_close($this->socket);
    }

    /**
     * @return string
     */
    public function read()
    {
        return trim(socket_read($this->socket, 512, PHP_NORMAL_READ));
    }

    public function send($data)
    {
        socket_write($this->socket, $data, 512);
    }


    private function throwError()
    {
        $error = socket_last_error($this->socket);
        die(socket_strerror($error));
    }
}