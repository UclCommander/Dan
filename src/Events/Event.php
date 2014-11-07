<?php namespace Dan\Events; 


use Dan\Core\Console;

class Event {

    /**
     * @var array
     */
    protected static $events = [];

    /**
     * Fires an event.
     *
     * @param $name
     * @param $data
     */
    public static function fire($name, ...$data)
    {
        Console::text("FIRING EVENT {$name}")->alert()->debug()->push();

        if(array_key_exists($name, static::$events))
        {
            foreach(static::$events[$name] as $list)
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

                        $response = $object->$method($data);
                    }
                    else
                        $response = $event($data);

                    if($response === false)
                        return;
                }
            }
        }
    }

    /**
     * Adds an event to the listen list.
     *
     * @param $name
     * @param $function
     * @param int $priority
     */
    public static function listen($name, $function, $priority = 5)
    {
        Console::text("Adding event: {$name} - PRIORITY LEVEL {$priority}")->alert()->debug()->push();

        if(!array_key_exists($name, static::$events))
            static::$events[$name] = [];

        $list = static::$events[$name];
        $list[$priority][] = $function;
        ksort($list);
        static::$events[$name] = $list;
    }
}
 