<?php namespace Dan\Plugins; 


use Dan\Events\Event;
use Dan\Events\EventPriority;

abstract class Plugin {

    /** @var Event[] $events  */
    protected $events = [];

    /**
     * @param $event
     * @param $func
     * @param int $priority
     */
    public function subscribe($event, $func, $priority = EventPriority::Normal)
    {
        $this->events[] = subscribe($event, $func, $priority);
    }

    /**
     *
     */
    public function unload()
    {
        foreach($this->events as $event)
            $event->destroy();
    }


    public abstract function load();
}