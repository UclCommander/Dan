<?php

namespace Dan\Events;

use Illuminate\Support\Collection;

class Handler
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $names = [];

    /**
     * @var array
     */
    protected $priorities = [];

    public function __construct()
    {
        $this->events = new Collection();
        $this->names = new Collection();
        $this->priorities = new Collection();
    }

    /**
     * @param $name
     * @param $handler
     * @param $priority
     *
     * @return \Dan\Events\Event
     */
    public function subscribe($name, $handler, $priority = Event::Normal) : Event
    {
        $event = new Event($name, $handler, $priority);

        $this->events->put($event->id, $event);
        $this->names->put($event->id, $name);
        $this->priorities->put($event->id, $priority);

        console()->debug("Creating event {$name} - ID: {$event->id} - Priority: {$priority}");

        return $event;
    }

    /**
     * @param $name
     * @param array $args
     *
     * @return mixed
     */
    public function fire($name, $args = [])
    {
        console()->debug("Firing all subscriptions to event {$name}");

        $keys = $this->names->filter(function ($item) use ($name) {
            if ($item == $name) {
                return $item;
            }
        })->keys()->toArray();

        if (empty($keys)) {
            return;
        }

        $priorities = $this->priorities->only($keys)->toArray();

        arsort($priorities);

        foreach ($priorities as $key => $priority) {
            /** @var Event $event */
            $event = $this->events->get($key);

            console()->debug("Calling event {$this->names[$key]} - ID: {$key}");

            $result = $event->call($args);

            if ($result === false) {
                return false;
            }

            if ($result instanceof EventArgs) {
                $args = $result;
                continue;
            }

            if (!empty($result)) {
                return $result;
            }
        }

        return $args;
    }

    /**
     * Destroys an event by object or id.
     *
     * @param $event
     */
    public function destroy($event)
    {
        if ($event instanceof Event) {
            $event = $event->id;
        }

        console()->debug("Destroying event {$this->names[$event]} - ID: {$event}");
        unset($this->events[$event], $this->names[$event], $this->priorities[$event]);
    }
}
