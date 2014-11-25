<?php namespace Dan\Events; 


use Dan\Core\Console;

class Event {

    /**
     * @var array
     */
    protected static $events = [];

    protected $id       = "";
    protected $name     = "";
    protected $function = "";
    protected $priority = 0;

    public function __construct($name, $function, $priority)
    {
        $this->id       = md5(microtime() . $name . $priority);
        $this->name     = $name;
        $this->function = $function;
        $this->priority = $priority;
    }

    /**
     * Adds an event.
     */
    public function add()
    {
        Console::text("Adding event: {$this->name} - PRIORITY LEVEL {$this->priority}")->alert()->debug()->push();

        if(!array_key_exists($this->name, static::$events))
            static::$events[$this->name] = [];

        $list = static::$events[$this->name];
        $list[$this->priority][$this->id] = $this->function;
        krsort($list);
        static::$events[$this->name] = $list;
    }

    /**
     * Destroys the event.
     */
    public function destroy()
    {
        Console::text("Destroying event: {$this->name}")->alert()->debug()->push();

        unset(static::$events[$this->name][$this->priority][$this->id]);
    }

    /**
     * Fires an event.
     *
     * @param string $name
     * @param array $data
     */
    public static function fire($name, array $data)
    {
        Console::text("FIRING EVENT {$name}")->alert()->debug()->push();

        if(array_key_exists($name, static::$events))
        {
            foreach(static::$events[$name] as $priority => $list)
            {
                foreach($list as $event)
                {
                    if(is_array($event))
                    {
                        //we need both a class and a method.
                        if(count($event) < 2)
                            continue;

                        //not an object? bail.
                        if(!is_object($event[0]))
                            continue;

                        $object = $event[0];
                        $method = $event[1];

                        Console::text("Calling $method from " . get_class($object) . ", priority {$priority}")->info()->debug()->push();

                        $response = $object->$method(new EventArgs($data));
                    }
                    else
                    {
                        Console::text("Calling closure event, priority {$priority}")->info()->debug()->push();
                        $response = $event(new EventArgs($data));
                    }

                    Console::text("Event returned {$response}")->info()->debug()->push();

                    if($response === false)
                    {
                        Console::text("Event stopped execution of further events.")->info()->debug()->push();
                        return;
                    }
                }
            }
        }
    }

    /**
     * Adds an event to the listen list.
     *
     * @param     $name
     * @param     $function
     * @param int $priority
     * @return \Dan\Events\Event
     */
    public static function listen($name, $function, $priority = 5)
    {
        $event = new static($name, $function, $priority);

        $event->add();

        return $event;
    }
}
 