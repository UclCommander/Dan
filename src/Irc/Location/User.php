<?php namespace Dan\Irc\Location; 


use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Storage\Storage;

class User extends Location {

    protected $username = null;
    protected $userHost = null;
    protected $realName = null;

    /** @var Storage  */
    protected $storage;

    public function __construct($nick, $user = null, $host = null)
    {
        parent::__construct();

        if(is_array($nick))
            throw new \Exception();

        $this->storage = Storage::load('users');

        if($this->storage->has($nick))
        {
            $data = $this->storage->get($nick);

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

    public function save()
    {
        $this->storage->add($this->name, [
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
     * @param $host
     */
    public function setHost($host)
    {
        $this->userHost = $host;
    }

    /**
     * Sets the user's real name.
     *
     * @param $name
     */
    public function setRealName($name)
    {
        $this->realName = $name;
    }
}