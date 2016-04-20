<?php

namespace Dan\Irc\Location;

use Dan\Contracts\UserContract;
use Dan\Database\Savable;
use Dan\Database\Traits\Data;
use Dan\Irc\Connection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class User extends Location implements Savable, Arrayable, UserContract
{
    use Data;

    /**
     * @var int
     */
    protected $id;

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
            $real = $nick[3] ?? $nick['real'] ?? null;
            $host = $nick[2] ?? $nick['host'] ?? null;
            $user = $nick[1] ?? $nick['user'] ?? null;
            $nick = $nick[0] ?? $nick['nick'] ?? null;
        }

        if ($user == null) {
            /** @var Collection $data */
            $data = $connection->database('users')->where('nick', $nick)->first();

            if (is_null($data->get('user')) && !is_null($nick)) {
                $connection->send('WHO', $nick);
            } else {
                $id = $data->get('id');
                $user = $data->get('user');
                $host = $data->get('host');
                $real = $data->get('real');
            }
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
     * Updates the users host.
     *
     * @param $host
     */
    public function setHost($host)
    {
        $this->host = $host;
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
        $this->connection->database('users')
            ->insertOrUpdate([
                'nick', '=', $this->nick,
            ], $this->toArray());

        $data = $this->connection->database('users')->where('nick', $this->nick)->first();
        $this->id = $data->get('id');
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
     * @return string
     */
    public function __toString()
    {
        return "{$this->nick}!{$this->user}@{$this->host}";
    }

    /**
     * @param bool $nick
     * @param bool $user
     * @param bool $host
     *
     * @return string
     */
    public function mask($nick = true, $user = true, $host = true)
    {
        $mask[0] = $nick ? $this->nick : '*';
        $mask[1] = $user ? $this->user : '*';
        $mask[2] = $host ? $this->host : '*';

        return "{$mask[0]}!{$mask[1]}@{$mask[2]}";
    }
}
