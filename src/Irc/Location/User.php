<?php

namespace Dan\Irc\Location;

use Dan\Contracts\UserContract;
use Dan\Database\Savable;
use Dan\Database\Traits\Data;
use Dan\Irc\Connection;
use Illuminate\Contracts\Support\Arrayable;

class User extends Location implements Savable, Arrayable, UserContract
{
    use Data;

    /**
     * @var string
     */
    protected $nick;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $real;

    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * User constructor.
     *
     * @param \Dan\Irc\Connection $connection
     * @param $nick
     * @param null $user
     * @param null $host
     * @param null $real
     */
    public function __construct(Connection $connection, $nick, $user = null, $host = null, $real = null)
    {
        if (is_array($nick)) {
            $real = $nick[3] ?? null;
            $host = $nick[2] ?? null;
            $user = $nick[1] ?? null;
            $nick = $nick[0] ?? null;
        }

        $this->location = $nick;
        $this->nick = $nick;
        $this->user = $user;
        $this->host = $host;
        $this->real = $real;
        $this->connection = $connection;

        if (!is_null($nick)) {
            $this->save();
        }
    }

    /**
     * Gets a user property.
     *
     * @param $name
     *
     * @return string|Connection
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new \InvalidArgumentException("Property {$name} doesn't exist.");
    }

    /**
     * Saves the user to the database.
     */
    public function save()
    {
        database($this->connection->getName())
            ->table('users')
            ->insertOrUpdate([
                'nick', '=', $this->nick,
            ], $this->toArray());
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'nick' => $this->nick,
            'user' => $this->user,
            'host' => $this->host,
            'real' => $this->real,
            'data' => $this->data,
        ];
    }

    /**
     *
     */
    public function __toString()
    {
        return "{$this->nick}!{$this->user}@{$this->host}";
    }
}
