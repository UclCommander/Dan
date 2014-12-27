<?php namespace Dan\Events; 


use Dan\Core\Console;
use Dan\Core\ConsoleColor;
use Illuminate\Support\Arr;

class Event {

    /** @var array $listeners */
    protected static $listeners = [];

    /** @var string $name */
    protected $name;

    /** @var callable|array */
    protected $function;

    /** @var int $priority */
    protected $priority;

    /** @var bool $once */
    protected $once;

    /** @var string $id */
    protected $id;

    /**
     * @param string  $name
     * @param callable|array  $function
     * @param int  $priority
     * @param string  $id
     * @param bool  $once
     */
    public function __construct($name, $function, $priority = EventPriority::Normal, $id, $once = false)
    {
        $this->name     = $name;
        $this->priority = $priority;
        $this->function = $function;
        $this->id       = $id;
        $this->once     = $once;

    }

    /**
     * Calls the function.
     *
     * @param EventArgs $eventArgs
     * @return mixed
     */
    public function call(EventArgs $eventArgs)
    {
        return call_user_func($this->function, $eventArgs);
    }

    /**
     * Checks to see if this was a one time call.
     *
     * @return bool
     */
    public function isOnce()
    {
        return $this->once;
    }

    /**
     * Get the event priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the event ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Destroys an event.
     */
    public function destroy()
    {
        unset(static::$listeners[$this->name][$this->priority][$this->id]);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static methods.
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Listens for an event.
     *
     * @param string $name
     * @param callable|array $function
     * @param int $priority
     */
    public static function subscribe($name, $function, $priority = EventPriority::Normal)
    {
        $id = static::makeId($name, $priority);
        static::$listeners[$name][$priority][$id] = new static($name, $function, $priority, $id);

        Console::text("Subscribing for event {$name} - Priority {$priority} - Event ID: {$id}")->debug()->alert()->push();
    }

    /**
     * Listens for an event once.
     *
     * @param string $name
     * @param callable|array $function
     * @param int $priority
     */
    public static function subscribeOnce($name, $function, $priority = EventPriority::Normal)
    {
        $id = static::makeId($name, $priority);
        static::$listeners[$name][$priority][$id] = new static($name, $function, $priority, $id, true);

        Console::text("Subscribing ONCE for event {$name} - Priority {$priority} - Event ID: {$id}")->debug()->alert()->push();
    }

    /**
     * Fires an event.
     *
     * @param string $name
     * @param \Dan\Events\EventArgs $args
     * @param bool $halt
     * @return array|null
     */
    public static function fire($name, EventArgs $args, $halt = false)
    {
        Console::text("Firing event {$name}")->debug()->alert()->push();

        $responses  = [];
        $events     = static::getListeners($name);

        foreach($events as $event)
        {
            $response = $event->call($args);

            // If its a one-time event, remove it from the listeners.
            if($event->isOnce())
            {
                Console::text("Destroying one-time event for {$name} - ID: {$event->getId()}")->debug()->alert()->push();
                unset(static::$listeners[$name][$event->getPriority()][$event->getId()]);
            }

            if(!is_null($response) && $halt)
            {
                Console::text("Halting execution of further events for {$name} - Halted firing function.")->debug()->alert()->push();
                return $response;
            }

            if($response == false && $event->getPriority() !== EventPriority::Critical)
            {
                Console::text("Halting execution of further events for {$name} - Halted by event ID {$event->getId()}")->debug()->alert()->push();
                break;
            }

            $responses[] = $response;
        }

        return $halt ? null : $responses;
    }


    /**
     * Gets all the listeners for the given event.
     *
     * @param $name
     * @return static[]
     */
    private static function getListeners($name)
    {
        if(!array_key_exists($name, static::$listeners))
            return [];

        $events = static::$listeners[$name];

        krsort($events);

        return Arr::flatten($events);
    }

    /**
     * @param $name
     * @param $priority
     * @return string
     */
    private static function makeId($name, $priority)
    {
        return md5(time().$name.$priority.microtime());
    }
}
 