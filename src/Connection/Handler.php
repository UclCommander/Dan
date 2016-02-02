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
     * @param \Dan\Contracts\ConnectionContract $connectionContract
     */
    public function addConnection(ConnectionContract $connectionContract)
    {
        $this->connections->put($connectionContract->getName(), $connectionContract);

        if ($this->running) {
            $connectionContract->connect();
        }
    }

    /**
     * Removes a connection from the reader.
     *
     * @param $name
     *
     * @return bool
     */
    public function removeConnection($name)
    {
        if ($name instanceof ConnectionContract) {
            $name = $name->getName();
        }

        if (!$this->connections->has($name)) {
            return false;
        }

        /** @var ConnectionContract $connection */
        $connection = $this->connections->get($name);

        $connection->disconnect();

        $this->connections->forget($name);

        return true;
    }

    /**
     *
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
     *
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
     * @param null $name
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
