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
     * @param array $styles
     */
    public function message($message, $styles = [])
    {
        message($this, $message, $styles);
    }

    /**
     * Sends an action to this location.
     *
     * @param $message
     */
    public function action($message)
    {
        action($this, $message);
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
     * Sets a mode on this location.
     *
     * @param $mode
     * @internal param $message
     */
    public function mode($mode)
    {
        connection()->send("MODE", $this->location, $mode);
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