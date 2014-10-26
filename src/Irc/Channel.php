<?php namespace Dan\Irc; 


class Channel {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $titleDate;

    /**
     * @var string
     */
    protected $titleSetter;

    /**
     * @var array
     */
    protected $users = [];

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
     * Clears the user list.
     */
    public function clearUsers()
    {
        $this->users = [];
    }

    /**
     * Gets the channel users.
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
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
     * Sets the channel title.
     *
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param $names
     */
    public function setNames($names)
    {
        $names = explode(' ', $names);

        foreach($names as $name)
        {
            $prefix     = substr($name, 0, 1);
            $prefixList = $this->connection->getSupport('PREFIX')[1];

            if(in_array($prefix, $prefixList))
                $this->users[substr($name, 1, strlen($name))] = $prefix;
            else
                $this->users[$name] = null;
        }
    }

    /**
     * Sets the title information
     *
     * @param $user
     * @param $date
     */
    public function setTitleInfo($user, $date)
    {
        $this->titleSetter  = $user;
        $this->titleDate    = $date;
    }

    /**
     * Sends a message to the channel.
     *
     * @param $message
     */
    public function sendMessage($message)
    {
        $this->connection->sendMessage($this->name, $message);
    }
}
 