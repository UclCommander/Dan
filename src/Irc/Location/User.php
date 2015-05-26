<?php namespace Dan\Irc\Location;


use Dan\Database\Savable;
use Illuminate\Contracts\Support\Arrayable;

class User extends Location implements Savable, Arrayable {

    protected $nick;
    protected $user;
    protected $host;
    protected $rank;

    public function __construct(array $data)
    {
        parent::__construct();

        $this->nick     = $data['nick'];
        $this->user     = $data['user'];
        $this->host     = $data['host'];
        $this->location = $data['nick'];
        $this->rank     = isset($data['rank']) ? $data['rank'] : null;

        if($this->rank != null)
            $this->setPrefix($this->rank);

        $this->save();
    }

    /**
     * @return string
     */
    public function string()
    {
        return "{$this->nick}!{$this->user}@{$this->host}";
    }

    /**
     * @return string
     */
    public function nick()
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     *
     */
    public function save()
    {
        database()->insertOrUpdate('users', ['nick' => $this->nick], [
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