<?php namespace Dan\Irc\Location;


use Dan\Database\Savable;
use Illuminate\Contracts\Support\Arrayable;

class User extends Location implements Savable, Arrayable {

    protected $nick;
    protected $user;
    protected $host;
    protected $rank;

    protected $save = true;

    public function __construct(array $data, $save = true)
    {
        parent::__construct();

        $this->nick     = $data['nick'];
        $this->user     = $data['user'];
        $this->host     = $data['host'];
        $this->location = $data['nick'];
        $this->rank     = isset($data['rank']) ? $data['rank'] : null;

        $this->save     = $save;

        if($this->rank != null)
            $this->setPrefix($this->rank);

        $this->save();
    }

    /**
     * Gets the user as a Nick!User@Host string.
     *
     * @return string
     */
    public function string()
    {
        return "{$this->nick}!{$this->user}@{$this->host}";
    }

    /**
     * Gets the users nickname.
     *
     * @return string
     */
    public function nick()
    {
        return $this->nick;
    }

    /**
     * Gets the users username.
     *
     * @return string
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Gets the users host.
     *
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Saves the user to the database.
     */
    public function save()
    {
        if(!$this->save)
            return;

        database()->table('users')->insertOrUpdate(['nick', $this->nick], [
           'nick' => $this->nick,
           'user' => $this->user,
           'host' => $this->host,
        ]);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'nick'  => $this->nick,
            'user'  => $this->user,
            'host'  => $this->host,
            'rank'  => $this->modes->implode(''),
        ];
    }
}