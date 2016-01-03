<?php

namespace Dan\Contracts;

interface SocketContract
{
    /**
     * Gets the socket name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the socket stream.
     *
     * @return resource
     */
    public function getStream();

    /**
     * Handles the socket resource .
     *
     * @param $resource
     */
    public function handle($resource);

    /**
     * Stops the current connection.
     *
     * @param string $reason
     *
     * @return mixed
     */
    public function quit($reason = null);
}
