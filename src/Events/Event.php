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
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var callable
     */
    protected $function;

    /**
     * @var int
     */
    protected $priority;

    /**
     * Event constructor.
     *
     * @param $type
     * @param $function
     * @param int $priority
     */
    public function __construct($type, $function = null, $priority = self::Normal)
    {
        $this->type = $type;
        $this->function = $function;
        $this->priority = $priority;

        $this->id = md5(microtime().$this->type.$this->priority);
    }

    /**
     * @param $name
     *
     * @return \Dan\Events\Event
     */
    public function name($name) : Event
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param $regex
     *
     * @return \Dan\Events\Event
     */
    public function match($regex) : Event
    {
        $this->regex = $regex;

        return $this;
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
        $this->priority = (int) $priority;

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }
}
