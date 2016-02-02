<?php

namespace Dan\Irc\Location;

use Dan\Irc\Connection;

class Channel extends Location
{
    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * Channel constructor.
     *
     * @param \Dan\Irc\Connection $connection
     * @param $name
     */
    public function __construct(Connection $connection, $name)
    {
        $this->connection = $connection;
        $this->location = $name;
    }
}
