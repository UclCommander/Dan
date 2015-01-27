<?php namespace Dan\Irc\Location; 

use Dan\Storage\Storage;

class User extends Location {

    protected $username = null;
    protected $userHost = null;
    protected $realName = null;

    /** @var Storage  */
    protected static $storage = null;

    public function __construct($nick, $user = null, $host = null)
    {
        parent::__construct();

        if(is_array($nick))
            throw new \Exception();

        if(static::$storage == null)
            static::$storage = Storage::load('users');

        if(static::$storage->has($nick))
        {
            $data = static::$storage->get($nick);

            $this->name         = $data['nick'];
            $this->username     = $data['user'];
            $this->userHost     = $data['host'];
            $this->realName     = $data['realName'];
        }
        else
            $this->connection->send('WHO', $nick);

        $this->name     = $nick;

        if($user != null)
            $this->username = $user;

        if($host != null)
            $this->userHost = $host;

        $this->save();
    }

    /**
     * Saves user changes.
     */
    public function save()
    {
        static::$storage->add($this->name, [
            'nick'     => $this->name,
            'user'     => $this->username,
            'host'     => $this->userHost,
            'realName' => $this->realName
        ])->save();
    }

    /**
     * Gets the user's nickname.
     *
     * @return mixed
     */
    public function getNick()
    {
        return $this->name;
    }

    /**
     * Gets the user's username.
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->username;
    }

    /**
     * Gets the user's host.
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->userHost;
    }

    /**
     * Gets the user's nickname.
     *
     * @return mixed
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Sets the user's host.
     *
     * @param $nick
     */
    public function setNick($nick)
    {
        $this->name = $nick;
        $this->save();
    }

    /**
     * Sets the user's host.
     *
     * @param $host
     */
    public function setHost($host)
    {
        $this->userHost = $host;
        $this->save();
    }

    /**
     * Sets the user's real name.
     *
     * @param $name
     */
    public function setRealName($name)
    {
        $this->realName = $name;
        $this->save();
    }
}