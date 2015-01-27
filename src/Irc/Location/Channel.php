<?php namespace Dan\Irc\Location; 

use Illuminate\Support\Collection;

class Channel extends Location {

    /** @var Collection  */
    protected $users;

    public function __construct($name)
    {
        parent::__construct();

        $this->name     = $name;
        $this->users    = new Collection();
    }

    /**
     * Adds a user.
     *
     * @param User $user
     */
    public function addUser(User $user)
    {
        $this->users->put($user->getNick(), $user);
    }

    /**
     * Removes a user.
     *
     * @param User|string $user
     */
    public function removeUser($user)
    {
        if($user instanceof User)
            $user = $user->getName();

        $this->users->forget($user);
        unset($user);
    }

    /**
     * Checks to see if the channel contains a user.
     *
     * @param string|User $user
     * @return User|null
     */
    public function hasUser($user)
    {
        if($user instanceof User)
            $user = $user->getNick();

        return $this->users->has($user);
    }

    /**
     * Gets a user.
     *
     * @param string|User $nick
     * @return User|null
     */
    public function getUser($nick)
    {
        if($nick instanceof User)
            $nick = $nick->getNick();

        return $this->users->get($nick);
    }

    /**
     * Gets all users in channel.
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

}