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
