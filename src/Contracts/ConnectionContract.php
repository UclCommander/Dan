<?php

namespace Dan\Contracts;

interface ConnectionContract
{
    /**
     * The name of the connection.
     *
     * @return string
     */
    public function getName();

    /**
     * Connects to the connection.
     *
     * @return void
     */
    public function connect();

    /**
     * Disconnects from the connection.
     *
     * @return bool
     */
    public function disconnect() : bool;

    /**
     * Reads the resource.
     *
     * @param resource $resource
     *
     * @return void
     */
    public function read($resource);

    /**
     * Writes to the resource.
     *
     * @param $line
     *
     * @return void
     */
    public function write($line);

    /**
     * Gets the stream resource for the connection.
     *
     * @return resource
     */
    public function getStream();
}
