<?php

namespace Dan\Connection;

use Dan\Contracts\ConnectionContract;
use Illuminate\Support\Collection;

class Handler
{
    /** @var Collection $connections */
    protected $connections;

    /**
     * @var bool
     */
    protected $running = false;

    public function __construct()
    {
        $this->connections = new Collection();
    }

    /**
     * Adds a connection to the handler.
     *
     * @param \Dan\Contracts\ConnectionContract $connectionContract
     *
     * @return bool|null
     */
    public function addConnection(ConnectionContract $connectionContract)
    {
        $this->connections->put($connectionContract->getName(), $connectionContract);

        if ($this->running) {
            return $connectionContract->connect();
        }

        return null;
    }

    /**
     * Removes a connection from the reader.
     *
     * @param $name
     *
     * @return bool
     */
    public function removeConnection($name) : bool
    {
        if ($name instanceof ConnectionContract) {
            $name = $name->getName();
        }

        if (!$this->hasConnection($name)) {
            return false;
        }

        /** @var ConnectionContract $connection */
        $connection = $this->connections->get($name);
        if (!$connection->disconnect()) {
            return false;
        }

        $this->forgetConnection($name);

        return true;
    }

    /**
     * Removes a connection from the handler.
     *
     * @param $name
     */
    public function forgetConnection($name)
    {
        if ($name instanceof ConnectionContract) {
            $name = $name->getName();
        }

        $this->connections->forget($name);
    }

    /**
     * Checks to see if a connection exists.
     *
     * @param $name
     *
     * @return bool
     */
    public function hasConnection($name) : bool
    {
        return $this->connections->has($name);
    }

    /**
     * Disconnects from all connections.
     *
     * @param bool $quit
     *
     * @return bool
     */
    public function disconnectFromAll($quit = false)
    {
        if ($quit) {
            $this->running = false;
        }

        foreach ($this->connections as $connection) {
            if (!$this->removeConnection($connection)) {
                if (!fclose($connection->getStream())) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Starts the connections.
     */
    public function start()
    {
        $this->running = true;

        foreach ($this->connections as $connection) {
            /* @var ConnectionContract $connection */
            $connection->connect();
        }
    }

    /**
     * Stops everything.
     */
    public function stop()
    {
        $this->running = false;
        $this->disconnectFromAll();
    }

    /**
     * Reads all stream connections.
     */
    public function readConnections()
    {
        while ($this->running) {
            usleep(200000);

            $inputs = $this->getStreams();
            $write = null;
            $except = null;

            if (stream_select($inputs, $write, $except, 0) > 0) {
                foreach ($inputs as $input) {
                    foreach ($this->connections as $connection) {
                        /** @var ConnectionContract $connection */
                        if ($input == $connection->getStream()) {
                            $connection->read($input);
                        }
                    }
                }
            }
        }
    }

    /**
     * Gets all connections or the one specified.
     *
     * @param string $name
     *
     * @return array
     */
    public function connections($name = null)
    {
        if (!is_null($name)) {
            return $this->connections->get($name);
        }

        return $this->connections;
    }

    /**
     * Gets all connection streams.
     *
     * @return array
     */
    protected function getStreams() : array
    {
        $streams = [];

        foreach ($this->connections->all() as $connection) {
            /* @var ConnectionContract $connection */
            $streams[] = $connection->getStream();
        }

        return $streams;
    }
}
