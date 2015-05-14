<?php namespace Dan\Irc\Location;


class Channel extends Location {

    protected $users = [];

    public function __construct($name)
    {
        parent::__construct();

        $this->name     = $name;
        $this->location = $name;


        database()->insertOrUpdate('channels', ['name' => $name], [
           'name'   => $name
        ]);
    }

    /**
     * @param $user
     * @return \Dan\Irc\Location\User|null
     */
    public function getUser($user)
    {
        if(!array_key_exists($user, $this->users))
            return null;

        $userinfo = database()->get('users', ['nick' => $user]);

        $userinfo['rank'] = $this->users[$user];

        return new User($userinfo);
    }

    /**
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

            $this->users[$nick] = $rank;
        }

        $channel = database()->get('channels', ['name' => $this->name]);

        if($channel['max_users'] < count($this->users))
        {
            database()->update('channels', ['name' => $this->name], [
                'max_users' => count($this->users)
            ]);
        }

        return array_keys($this->users);
    }
}