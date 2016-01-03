<?php

namespace Dan\Hooks\Types;

use Dan\Contracts\HookTypeContract;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Irc\Location\Location;

class EventHook implements HookTypeContract
{
    /**
     * @var object
     */
    protected $class;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \Dan\Events\Event[]
     */
    protected $events;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param $name
     * @param array $events
     * @param array $settings
     */
    public function __construct($name, array $events, array $settings = [])
    {
        $this->name = $name;
        $this->settings = $settings;

        foreach ($events as $event) {
            $this->events[] = subscribe($event, [$this, 'run'], isset($settings['priority']) ? $settings['priority'] : EventPriority::Normal);
        }
    }

    /**
     * @return \Dan\Events\Event[]
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * Registers an anonymous class to the hook.
     *
     * @param $anonymous
     *
     * @return void
     */
    public function anon($anonymous)
    {
        $this->class = $anonymous;
    }

    /**
     * Registers an anonymous function to the hook.
     *
     * @param callable $callable
     *
     * @return void
     */
    public function func(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Runs the hook.
     *
     * @param EventArgs $args
     *
     * @return bool
     */
    public function run($args)
    {
        // Check to see if this is running in a channel, if so, check for disabled hooks and ignore those.
        if (isset($args['channel'])) {
            $info = database()->table('channels')->where('name', $args['channel']->getLocation())->first()->get('info');
            $except = isset($info['disabled_hooks']) ? $info['disabled_hooks'] : [];

            if (in_array($this->name, $except)) {
                return;
            }
        }

        try {
            if ($this->class != null) {
                $class = $this->class;
                $method = isset($this->settings['method']) ? $this->settings['method'] : 'run';

                return $class->$method($args);
            }

            if ($this->callable != null) {
                $func = $this->callable;

                return $func($args);
            }
        } catch (\Error $error) {
            error($error->getMessage());

            if (DEBUG) {
                error($error->getFile().':'.$error->getLine());
            }

            if (isset($args['channel']) && $args['channel'] instanceof Location) {
                $args['channel']->message('Something unexpected has happened!');
            }

            return false;
        } catch (\Exception $e) {
            error($e->getMessage());

            if (DEBUG) {
                error($e->getFile().':'.$e->getLine());
            }

            if (isset($args['channel']) && $args['channel'] instanceof Location) {
                $args['channel']->message('Something unexpected has happened!');
            }

            return false;
        }
    }
}
