<?php namespace Dan\Irc; 


class Channel {

    protected $name;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param $connection
     * @param $name
     */
    public function __construct(Connection &$connection, $name)
    {
        $this->setName($name);
        $this->connection = $connection;
    }

    /**
     * Sets the channel name.
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sends message(s) to the channel.
     *
     * @param $message
     */
    public function sendMessage(...$message)
    {
        $this->connection->sendMessage($this->name, $message);
    }
}
 