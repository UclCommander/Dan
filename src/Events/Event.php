<?php namespace Dan\Events;


use Closure;

class Event {

    protected static $events = [];
    protected $name;
    protected $priority;
    protected $id;
    protected $function;

    public function __construct($name, $function, $priority = EventPriority::Normal)
    {
        $this->name     = $name;
        $this->function = $function;
        $this->priority = $priority;

        $this->id = md5(microtime().$this->name.$this->priority.($function instanceof Closure ? md5($function) : serialize($this->function)));

        static::$events[$name][$priority][$this->id] = $this;
    }

    /**
     * @param \Dan\Events\EventArgs $data
     * @return mixed
     */
    public function call(EventArgs $data)
    {
        return call_user_func_array($this->function, [$data]);
    }

    /**
     *
     */
    public function destroy()
    {
        debug("[EVENTS] Destroying event <yellow>{$this->id}</yellow> for <info>{$this->name}</info>");
        unset(static::$events[$this->name][$this->priority][$this->id]);
    }

    /**
     * @param $event
     * @return bool
     */
    public static function has($event)
    {
        return array_key_exists($event, static::$events);
    }

    /**
     * @param $name
     * @param $function
     * @param int $priority
     * @return static
     */
    public static function subscribe($name, $function, $priority = EventPriority::Normal)
    {
        return new static($name, $function, $priority);
    }

    /**
     * @param $event
     * @param $data
     * @return mixed
     */
    public static function fire($event, $data = null)
    {
        debug("Firing event <info>{$event}</info>");

        if(!static::has($event))
            return $data;

        $list = static::$events[$event];

        krsort($list);

        $data = new EventArgs($data);

        foreach($list as $priority => $events)
        {
            foreach($events as $id => $event)
            {
                $data->put('event', $event);
                /** @var static $event */
                $return = $event->call($data);

                if($return === false)
                    return false;

                if($return instanceof EventArgs)
                {
                    $data = $return;
                    continue;
                }

                if(!empty($return))
                    return $return;
            }
        }

        return null;
    }
}