<?php

namespace Dan\Web;

use Dan\Contracts\ConnectionContract;

class Listener implements ConnectionContract
{

    protected $stream;

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * The name of the connection.
     *
     * @return string
     */
    public function getName()
    {
        return 'listener';
    }

    /**
     * Gets the stream resource for the connection.
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Connects to the connection.
     *
     * @return bool
     * @throws \Exception
     */
    public function connect() : bool
    {
        console()->info('Starting socket listener on tcp://'.$this->config['host'].':'.$this->config['port']);
        $this->stream = stream_socket_server('tcp://'.$this->config['host'].':'.$this->config['port'], $errno, $errstr);

        if ($this->stream === false) {
            throw new \Exception($errstr);
        }

        stream_set_blocking($this->stream, 0);

        return true;
    }

    /**
     * Disconnects from the connection.
     *
     * @return bool
     */
    public function disconnect() : bool
    {
        fclose($this->stream);
        unset($this->stream);
    }

    /**
     * Reads the resource.
     *
     * @param resource $resource
     *
     * @return void
     */
    public function read($resource)
    {
        $client = stream_socket_accept($resource);

        if ($client === false) {
            return;
        }

        $client = new Client($this, $client);
        $client->handle();
    }

    /**
     * Writes to the resource.
     *
     * @param $line
     *
     * @return void
     */
    public function write($line)
    {
        // TODO: Implement write() method.
    }
}