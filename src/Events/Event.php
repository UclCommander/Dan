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

    /**
     * Event constructor.
     *
     * @param $name
     * @param $function
     * @param int $priority
     */
    public function __construct($name, $function = null, $priority = self::Normal)
    {
        $this->name = $name;
        $this->function = $function;
        $this->priority = $priority;

        $this->id = md5(microtime().$this->name.$this->priority);
    }

    /**
     * @param $handler
     *
     * @return \Dan\Events\Event
     */
    public function handler($handler) : Event
    {
        $this->function = $handler;
        return $this;
    }

    /**
     * @param $priority
     *
     * @return \Dan\Events\Event
     */
    public function priority($priority) : Event
    {
        $this->priority = (int)$priority;
        return $this;
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
        $func = $this->function;

        if (!($func instanceof \Closure) && !is_array($func)) {
            $func = [$this->function, 'run'];
        }

        return dan()->call($func, $args);
    }

    /**
     * Destroys the event.
     */
    public function destroy()
    {
        events()->destroy($this);
    }
}
