<?php namespace Dan\Irc; 


use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Illuminate\Support\Collection;

class Channel extends Sendable {

    /** @var Collection $users */
    protected $users;

    /** @var Event[] $events */
    protected $events = [];

    /** @var string $title */
    protected $title = '';

    /** @var string $titleSetter */
    protected $titleSetter = '';

    /** @var string $titleSetTime */
    protected $titleSetTime = '';


    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->users    = new Collection();
        $this->location = $name;

        Event::subscribe('irc.packet.mode', [$this, 'handleModeChange'], EventPriority::Critical);
    }

    /**
     * Adds a user to the channel.
     *
     * @param \Dan\Irc\User $user
     */
    public function addUser(User $user)
    {
        $this->users->put($user->getNick(), $user);
    }

    /**
     * Gets the channels name.
     * @return mixed
     */
    public function getName()
    {
        return $this->location;
    }

    /**
     * Sets the channel title.
     *
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param $user
     * @param $time
     */
    public function setTitleInfo($user, $time)
    {
        $this->titleSetter  = $user;
        $this->titleSetTime = $time;
    }

    /**
     * Handles mode changes for the channel.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     */
    public function handleModeChange(EventArgs $eventArgs)
    {
        var_dump($eventArgs);
    }
}
