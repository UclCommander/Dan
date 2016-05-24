<?php

namespace Dan\Events;

use Dan\Irc\Location\Channel;
use Illuminate\Support\Collection;

class Handler
{
    /**
     * @var Collection
     */
    protected $events;

    /**
     * @var Collection
     */
    protected $names;

    /**
     * @var Collection
     */
    protected $priorities;

    /**
     * @var Collection
     */
    protected $addonEvents;

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        $this->events = new Collection();
        $this->names = new Collection();
        $this->priorities = new Collection();
        $this->addonEvents = new Collection();

        $this->subscribe('addons.load', function () {
            foreach ($this->addonEvents as $id => $name) {
                $this->destroy($this->events[$id]);
            }

            $this->addonEvents = new Collection();
        });
    }

    /**
     * Updates a priority by id.
     *
     * @param $id
     * @param $priority
     */
    public function updatePriority($id, $priority)
    {
        $this->priorities->put($id, $priority);
        console()->debug("Event {$id} priority changed: {$priority}");
    }

    /**
     * @param $name
     * @param $handler
     * @param $priority
     *
     * @return \Dan\Events\Event
     */
    public function subscribe($name, $handler = null, $priority = Event::Normal) : Event
    {
        $event = new Event($name, $handler, $priority);

        $this->events->put($event->id, $event);
        $this->names->put($event->id, $name);
        $this->priorities->put($event->id, $priority);

        console()->debug("Creating event {$name} - ID: {$event->id} - Priority: {$priority}");

        return $event;
    }

    /**
     * Registers an addon event that will be automatically destroyed when addons are reloaded.
     *
     * @param $name
     *
     * @return \Dan\Events\Event
     */
    public function registerAddonEvent($name) : Event
    {
        console()->info("Registering addon event handler for {$name}");
        $event = $this->subscribe($name);
        $this->addonEvents->put($event->id, $name);

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
            return null;
        }

        $priorities = $this->priorities->only($keys)->toArray();

        arsort($priorities);

        foreach ($priorities as $key => $priority) {
            /** @var Event $event */
            $event = $this->events->get($key);

            if ($this->disabled($event, $key, $args)) {
                continue;
            }

            console()->debug("Calling event {$this->names[$key]} - ID: {$key}");

            if ($this->regexMatches($event, $args) === false) {
                continue;
            }

            $this->configureSettings($event, $args);

            $result = $event->call($args);

            if ($result === false) {
                return false;
            }

            if ($result instanceof EventArgs) {
                foreach ($result as $k => $v) {
                    $args[$k] = $v;
                }
                
                continue;
            }

            if (!is_bool($result) && !is_null($result)) {
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

        console()->debug("Destroying event {$this->names->get($event)} - ID: {$event}");
        $this->events->forget($event);
        $this->names->forget($event);
        $this->priorities->forget($event);
        $this->addonEvents->forget($event);
    }

    /**
     * @param \Dan\Events\Event $event
     * @param $key
     * @param $args
     *
     * @return bool
     */
    protected function disabled(Event $event, $key, $args) : bool
    {
        if (!isset($args['channel'])) {
            return false;
        }

        /** @var Channel $channel */
        $channel = $args['channel'];

        $disabled = $channel->getData('info.hooks.disabled', []);

        if (is_null($event->getName())) {
            return false;
        }

        return in_array($event->getName(), $disabled);
    }

    /**
     * @param \Dan\Events\Event $event
     * @param $args
     *
     * @return bool|null
     */
    protected function regexMatches(Event $event, &$args)
    {
        $regex = $event->getRegex();

        if (is_null($regex)) {
            return null;
        }

        if (!preg_match_all($regex, $args['message'], $matches)) {
            return false;
        }

        $args['matches'] = $matches;

        return true;
    }

    /**
     * @param \Dan\Events\Event $event
     * @param $args
     */
    protected function configureSettings(Event $event, &$args)
    {
        if (is_null($event->getName())) {
            return;
        }

        if (!array_key_exists('channel', $args)) {
            return;
        }

        if (empty($event->getSettings())) {
            return;
        }

        if (!$args['channel']->hasData("hooks.{$event->getName()}")) {
            $args['channel']->setData("hooks.{$event->getName()}", $event->getSettings())->save();
        }

        $args['settings'] = dotcollect($args['channel']->getData("hooks.{$event->getName()}"));
    }
}
