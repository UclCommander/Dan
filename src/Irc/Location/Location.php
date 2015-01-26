<?php namespace Dan\Irc\Location; 


use Dan\Irc\Connection;
use Dan\Irc\ModeObject;

abstract class Location extends ModeObject {

    protected $name = null;


    public function __construct()
    {
        parent::__construct();

        $this->connection = Connection::instance();
    }

    /**
     * Sets the location's name.
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the location's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the locations name.
     *
     * @return string|null
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Sends a message to this location.
     *
     * @param $message
     */
    public function sendMessage($message)
    {
        $this->connection->sendMessage($this, $message);
    }

    /**
     * Sends a notice to this location.
     *
     * @param $message
     */
    public function sendNotice($message)
    {
        $this->connection->sendNotice($this, $message);
    }
}