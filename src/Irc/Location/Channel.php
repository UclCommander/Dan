<?php namespace Dan\Irc\Location;


use Dan\Irc\Connection;
use Dan\Irc\ModeObject;
use Illuminate\Support\Collection;

class Channel extends Location {

    protected static $who = [];

    /** @var Collection $users */
    protected $users;

    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * @param \Dan\Irc\Connection $connection
     * @param $name
     * @throws \Exception
     */
    public function __construct(Connection $connection, $name)
    {
        parent::__construct();

        $this->users        = new Collection();
        $this->name         = $name;
        $this->location     = $name;
        $this->connection   = $connection;

        database()->table('channels')->insertOrUpdate(['name', $name], [
           'name'   => $name
        ]);
    }

    /**
     * Gets all users in the channel.
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users->all();
    }

    /**
     * Sets the channel topic.
     *
     * @param $topic
     */
    public function setTopic($topic)
    {
        $this->connection->send("TOPIC", $this->name, $topic);
    }

    /**
     * Gets a user.
     *
     * @param $user
     * @return \Dan\Irc\Location\User|null
     */
    public function getUser($user)
    {
        if($user instanceof User)
            $user = $user->getLocation();

        if(!$this->users->has($user))
            return null;

        $info = $this->users->get($user);

        $obj = user($info['nick']);

        $obj->setMode($info['modes']);

        return $obj;
    }

    /**
     * Checks to see if the channel has a user.
     *
     * @param $user
     * @return bool
     */
    public function hasUser($user)
    {
        if($user instanceof User)
            $user = $user->getLocation();

        return $this->users->has($user);
    }

    /**
     * Renames a user (used when a user uses NICK).
     *
     * @param $user
     * @param $new
     * @return null
     */
    public function renameUser($user, $new)
    {
        if($user instanceof User)
            $user = $user->getLocation();

        if(!$this->users->has($user))
            return null;

        $old = $this->users->get($user);

        $old['nick'] = $new;

        $this->users->put($new, $old);
        $this->removeUser($user);
    }

    /**
     * Removes a user.
     *
     * @param $user
     */
    public function removeUser($user)
    {
        if($user instanceof User)
            $user = $user->nick();

        $this->users->forget($user);
    }

    /**
     * Sets users from 353.
     *
     * @param $users
     * @return array
     */
    public function setUsers($users)
    {
        $users = explode(' ', $users);

        foreach($users as $user)
        {
            $rank = '';
            $nick = $user;

            if(!ctype_alnum(substr($user, 0, 1)))
            {
                $rank = substr($user, 0, 1);
                $nick = substr($user, 1);
            }

            $info = database()->table('users')->where('nick', $nick)->first();

            if(!$info->count())
            {
                if(!in_array($nick, static::$who))
                {
                    static::$who[$nick] = $nick;
                    // Database has no info, request it from IRC.
                    send("WHO", $nick);
                }

                // Set default
                $info['nick'] = $nick;
                $info['user'] = '';
                $info['host'] = '';
            }
            else
                unset(static::$who[$nick]);

            $mode = new ModeObject();
            $mode->setPrefix($rank);

            $this->users->put($nick , ['nick' => $info['nick'], 'modes' => $mode]);
        }

        $channel = database()->table('channels')->where('name', $this->name)->first();

        if($channel['max_users'] < count($this->users))
        {
            database()
                ->table('channels')
                ->where('name', $this->name)
                ->update([
                    'max_users' => $this->users->count()
                ]);
        }

        return array_keys($this->users->toArray());
    }

    /**
     * Updates user modes.
     *
     * @param array $data
     */
    public function updateUserModes(array $data)
    {
        foreach($data as $modes)
        {
            if(!$this->users->has($modes[0]))
                continue;

            $this->users[$modes[0]]['modes']->setMode($modes[1]);
        }
    }
}

