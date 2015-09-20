<?php namespace Dan\Irc\Location; 


use Dan\Contracts\MessagingContract;
use Dan\Irc\Connection;
use Dan\Irc\ModeObject;

class Location extends ModeObject implements MessagingContract {

    /**
     * @var Connection
     */
    protected $connection;

    protected $location;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sends a message to this location.
     *
     * @param $message
     * @param array $styles
     */
    public function message($message, $styles = [])
    {
        $this->connection->message($this, $message, $styles);
    }

    /**
     * Sends an action to this location.
     *
     * @param $message
     */
    public function action($message)
    {
        $this->connection->action($this, $message);
    }

    /**
     * Sends a notice to this location.
     *
     * @param $message
     */
    public function notice($message)
    {
        $this->connection->notice($this, $message);
    }

    /**
     * Sets a mode on this location.
     *
     * @param $mode
     * @internal param $message
     */
    public function mode($mode)
    {
        $this->connection->send("MODE", $this->location, $mode);
    }

    /**
     * Returns a IRC compatible endpoint location.
     *
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->location;
    }
}