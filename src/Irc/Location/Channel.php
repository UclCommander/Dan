<?php namespace Dan\Irc\Location;


use Dan\Irc\ModeObject;
use Illuminate\Support\Collection;

class Channel extends Location {

    /** @var Collection $users */
    protected $users;

    public function __construct($name)
    {
        parent::__construct();

        $this->users = new Collection();
        $this->name     = $name;
        $this->location = $name;

        database()->insertOrUpdate('channels', ['name' => $name], [
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
        return $this->users->toArray();
    }

    /**
     * Sets the channel topic.
     *
     * @param $topic
     */
    public function setTopic($topic)
    {
        send("TOPIC", $this->name, $topic);
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

            $info = database()->get('users', ['nick' => $nick]);

            if(empty($info))
            {
                // Database has no info, request it from IRC.
                send("WHO", $nick);

                // Set default
                $info['nick'] = $nick;
                $info['user'] = '';
                $info['host'] = '';
            }

            $mode = new ModeObject();
            $mode->setPrefix($rank);

            $this->users->put($nick , ['nick' => $info['nick'], 'modes' => $mode]);
        }

        $channel = database()->get('channels', ['name' => $this->name]);

        if($channel['max_users'] < count($this->users))
        {
            database()->update('channels', ['name' => $this->name], [
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

