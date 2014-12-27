<?php namespace Dan\Irc;

class User extends Sendable {

    protected $name;
    protected $host;


    public function __construct($nick = '', $name = '', $host = '')
    {
        $this->location = $nick;
        $this->name     = $name;
        $this->host     = $host;
    }

    /**
     * Gets the users nick
     *
     * @return string
     */
    public function getNick()
    {
        return $this->location;
    }

    /**
     * Gets the users username.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the users host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}


 