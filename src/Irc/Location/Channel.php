<?php

namespace Dan\Irc\Location;

use Dan\Irc\Connection;
use Illuminate\Support\Collection;

class Channel extends Location
{
    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * @var Collection
     */
    protected $users;

    /**
     * Channel constructor.
     *
     * @param \Dan\Irc\Connection $connection
     * @param $name
     */
    public function __construct(Connection $connection, $name)
    {
        $this->connection = $connection;
        $this->location = $name;

        $this->users = new Collection();
    }

    /**
     * Kicks a user from the channel.
     *
     * @param $user
     * @param string $reason
     */
    public function kick($user, $reason = 'Requested')
    {
        if ($user instanceof User) {
            $user = $user->nick;
        }

        $this->connection->send('KICK', $this, $user, $reason);
    }

    /**
     * Gets all users in the channel.
     *
     * @return \Illuminate\Support\Collection
     */
    public function users() : Collection
    {
        return $this->users->values();
    }

    /**
     * @param $user
     * @param string $prefix
     */
    public function addUser($user, $prefix = '')
    {
        if (!($user instanceof User)) {
            $user = new User($this->connection, $user);
        }

        $user->setPrefix($prefix);

        $this->users->put($user->nick, $user);
    }

    /**
     * Checks to see if the channel has the given user.
     *
     * @param $user
     *
     * @return bool
     */
    public function hasUser($user) : bool
    {
        if ($user instanceof User) {
            $user = $user->nick;
        }

        return $this->users->has($user);
    }

    /**
     * Gets a user from the channel.
     *
     * @param $user
     *
     * @return User|null
     */
    public function getUser($user)
    {
        if ($user instanceof User) {
            $user = $user->nick;
        }

        return $this->users->get($user);
    }

    /**
     * Sets a mode on a user.
     *
     * @param $user
     * @param $mode
     */
    public function setUserMode($user, $mode)
    {
        if ($user instanceof User) {
            $user = $user->nick;
        }

        $this->users->get($user)->setMode($mode);
    }
}
