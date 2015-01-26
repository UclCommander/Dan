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
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

}