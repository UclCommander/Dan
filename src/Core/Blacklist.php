<?php namespace Dan\Core; 


use Dan\Irc\Location\User;
use Dan\Storage\Storage;

class Blacklist {

    protected $data;

    public function __construct()
    {
        $this->data = new Storage('blacklist');
    }

    /**
     * Checks to see if a user is on the blacklist or not.
     *
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    public function check(User $user)
    {
        foreach($this->data->get() as $match)
            if (fnmatch($match, "{$user->getNick()}!{$user->getUser()}@{$user->getHost()}"))
                return true;

        return false;
    }

    /**
     * Adds a blacklist item.
     *
     * @param $user
     * @return string
     */
    public function add($user)
    {
        $str = $this->getUserString($user);

        $this->data->add($str, $str);
        $this->data->save();

        return $str;
    }

    /**
     * Removes a blacklist item.
     *
     * @param $user
     * @return string
     */
    public function remove($user)
    {
        $str = $this->getUserString($user);

        $this->data->remove($str);
        $this->data->save();

        return $str;
    }

    /**
     * Returns all blacklist items.
     *
     * @return array|null
     */
    public function all()
    {
        return $this->data->get();
    }

    /**
     * Gets a blacklist-friendly string.
     *
     * @param $user
     * @return string
     */
    public function getUserString($user)
    {
        if(strpos($user, '@') === 0)
            $user = "*{$user}";

        $matches = [];

        preg_match("/\!?\@?([a-zA-Z0-9-_*.]+)\!?([a-zA-Z0-9-_*.]+)?\@?([a-zA-Z0-9-_*.]+)?/", $user, $matches);

        $one    = empty($matches[1]) ? "*" : $matches[1];
        $two    = empty($matches[2]) ? "*" : $matches[2];
        $three  = empty($matches[3]) ? "*" : $matches[3];

        return "{$one}!{$two}@{$three}";
    }
}