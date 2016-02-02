<?php

namespace Dan\Events;

class Event
{
    const VeryHigh = 20;
    const High = 10;
    const AboveNormal = 8;
    const Normal = 5;
    const BelowNormal = 3;
    const Low = 1;

    public function __construct($name, $function, $priority = self::Normal)
    {
        $this->name = $name;
        $this->function = $function;
        $this->priority = $priority;

        $this->id = md5(microtime().$this->name.$this->priority);
    }

    /**
     * Calls the event.
     *
     * @param $args
     *
     * @return mixed
     */
    public function call($args)
    {
        return dan()->call($this->function, $args);
    }

    /**
     * Destroys the event.
     */
    public function destroy()
    {
        events()->destroy($this);
    }
}
