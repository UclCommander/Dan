<?php

namespace Dan\Irc\Location;

use Dan\Irc\Connection;
use Dan\Irc\Traits\Mode;

abstract class Location
{
    use Mode;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Sends a message to the location.
     *
     * @param $message
     * @param array $styles
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function message($message, $styles = [])
    {
        $this->connection->message($this, $message, $styles);

        return $this;
    }

    /**
     * Sends an action to the location.
     *
     * @param $message
     * @param array $styles
     *
     * @return $this
     */
    public function action($message, $styles = [])
    {
        $this->connection->action($this, $message, $styles);

        return $this;
    }

    /**
     * Sends a notice to the location.
     *
     * @param $message
     *
     * @return $this
     */
    public function notice($message)
    {
        $this->connection->notice($this, $message);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->location;
    }
}
