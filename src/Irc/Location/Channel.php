<?php

namespace Dan\Irc\Location;

use Carbon\Carbon;
use Dan\Database\Savable;
use Dan\Database\Traits\Data;
use Dan\Events\Event;
use Dan\Irc\Connection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Channel extends Location implements Savable, Arrayable
{
    use Data;

    /**
     * @var \Dan\Irc\Connection
     */
    protected $connection;

    /**
     * @var Collection
     */
    protected $users;

    /**
     * @var array
     */
    protected $moderations = ['mute' => [], 'ban' => []];

    /**
     * @var Event[]
     */
    protected $events = [];
    
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

        $this->loadCurrentData();

        $this->save();

        $this->events[] = events()->subscribe('irc.join', [$this, 'handleJoin']);
        $this->events[] = events()->subscribe('system.ping', [$this, 'handleSystemPing']);
    }

    //region users

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

        $this->users->put(strtolower($user->nick), $user);
    }

    /**
     * @param $user
     */
    public function removeUser($user)
    {
        if (!($user instanceof User)) {
            $user = new User($this->connection, $user);
        }

        $this->users->forget(strtolower($user->nick));
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

        return $this->users->has(strtolower($user));
    }

    /**
     * Gets a user from the channel.
     *
     * @param User|string $user
     *
     * @return User|null
     */
    public function getUser($user)
    {
        $nick = $user;

        if ($user instanceof User) {
            $nick = $user->nick;
        }

        /** @var User $current */
        $current = $this->users->get(strtolower($nick));

        if (is_null($current)) {
            return null;
        }

        if ($user instanceof User) {
            $user->setData($current->data)->setRawModes($current->modes);
            $this->users->put(strtolower($nick), $user);

            return $user;
        }

        return $current;
    }

    /**
     * @param $id
     *
     * @return \Dan\Irc\Location\User|null
     */
    public function getUserById($id)
    {
        $users = $this->connection->database('users')->where('id', $id);

        if ($users->count()) {
            return $this->getUser($users->first()->get('nick'));
        }

        return null;
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

        $this->users->get(strtolower($user))->setMode($mode);
    }

    //endregion

    #region moderation

    /**
     * @param \Dan\Irc\Location\User $user
     */
    public function handleJoin(User $user)
    {
        if (in_array($user->id, $this->moderations['mute'])) {
            $this->mode('-v', $user);
        }
    }

    /**
     *
     */
    public function handleSystemPing()
    {
        foreach ($this->getData('mute', []) as $id => $atom) {
            if ($atom == null) {
                continue;
            }

            if ((new Carbon())->diffInMinutes(new Carbon($atom), false) <= 0) {
                $this->unmute($this->getUserById($id));
            }
        }
    }

    /**
     * Mutes a user with an optional duration.
     *
     * @param $user
     * @param $duration
     */
    public function mute($user, $duration = null)
    {
        if ((!$user instanceof User)) {
            $user = $this->getUser($user);
        }

        $this->setData("mute.{$user->id}", $duration ? intervalTimeToCarbon($duration)->toAtomString() : null)->save();
        $this->mode('-v', $user);
    }

    /**
     * Un-mutes a user.
     *
     * @param $user
     */
    public function unmute($user)
    {
        if ((!$user instanceof User)) {
            $user = $this->getUser($user);
        }

        $this->forgetDataByKey("mute.{$user->id}")->save();
        $this->mode('+v', $user);
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

    #endregion

    /**
     * @param $topic
     */
    public function setTopic($topic)
    {
        $this->connection->send('TOPIC', $this, $topic);
    }

    /**
     * @param $mode
     * @param null $user
     */
    public function mode($mode, $user = null)
    {
        $this->connection->send('MODE', $this, $mode, $user);
    }

    //region channel data

    /**
     * Saves the channel to the database.
     */
    public function save()
    {
        $this->connection->database('channels')
            ->insertOrUpdate([
                'name', '=', $this->location,
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
            'name'      => $this->location,
            'max_users' => 0,
            'topic'     => '',
            'data'      => $this->data,
        ];
    }

    /**
     * Loads currenty known channel data.
     */
    protected function loadCurrentData()
    {
        /** @var Collection $data */
        $data = $this->connection->database('channels')->where('name', $this->location)->first();

        if (!$data->count()) {
            return;
        }

        $this->location = $data->get('name');
        //$this->maxUsers = $data->get('max_users');
        //$this->topic = $data->get('topic');
        $this->data = $data->get('data');
    }

    //endregion

    /**
     * Parts the channel.
     *
     * @param string $message
     */
    public function part($message = "Requested")
    {
        $this->connection->partChannel($this, $message);
    }

    /**
     * Destroys all event listeners for the channel.
     */
    public function destroy()
    {
        foreach ($this->events as $event) {
            $event->destroy();
        }
    }
}
