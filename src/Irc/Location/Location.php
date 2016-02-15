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
     */
    public function message($message, $styles = [])
    {
        $this->connection->message($this, $message, $styles);
    }

    /**
     * Sends an action to the location.
     *
     * @param $message
     * @param array $styles
     */
    public function action($message, $styles = [])
    {
        $this->connection->action($this, $message, $styles);
    }

    /**
     * Sends a notice to the location.
     *
     * @param $message
     */
    public function notice($message)
    {
        $this->connection->notice($this, $message);
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
