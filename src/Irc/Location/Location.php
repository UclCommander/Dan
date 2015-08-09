<?php namespace Dan\Irc\Location; 


use Dan\Irc\ModeObject;

class Location extends ModeObject {

    protected $location;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sends a message to this location.
     *
     * @param $message
     */
    public function message($message)
    {
        message($this, $message);
    }

    /**
     * Sends a notice to this location.
     *
     * @param $message
     */
    public function notice($message)
    {
        notice($this, $message);
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