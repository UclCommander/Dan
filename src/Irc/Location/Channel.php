<?php namespace Dan\Irc\Location; 

use Illuminate\Support\Collection;

class Channel extends Location {

    /** @var \Illuminate\Support\Collection  */
    protected $users;

    public function __construct($name)
    {
        parent::__construct();

        $this->name     = $name;
        $this->users    = new Collection();
    }

    /**
     * @param \Dan\Irc\Location\User $user
     */
    public function addUser(User $user)
    {
        $this->users->put($user->getNick(), $user);
    }

    /**
     * Gets a user.
     *
     * @param $nick
     * @return User|null
     */
    public function getUser($nick)
    {
        return $this->users->get($nick);
    }

    /**
     * Gets all users in channel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

}