<?php namespace Dan\Hooks\Types;


use Dan\Contracts\HookTypeContract;
use Dan\Events\EventArgs;

class EventHook implements HookTypeContract {

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
     * @var \Dan\Events\Event
     */
    protected $event;

    /**
     * @param $event
     */
    public function __construct($event)
    {
        $this->event = subscribe($event, [$this, 'run']);
    }

    /**
     * @return \Dan\Events\Event
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * Registers an anonymous class to the hook.
     *
     * @param $anonymous
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
     * @return bool
     */
    public function run($args)
    {
        try
        {
            if($this->class != null)
            {
                $class = $this->class;
                $method = isset($this->settings['method']) ? $this->settings['method'] : 'run';

                return $class->$method($args);
            }

            if($this->callable != null)
            {
                $func = $this->callable;

                return $func($args);
            }
        }
        catch(\Error $error)
        {
            $args['channel']->message("Something unexpected has happened!");
            error($error->getMessage());

            return false;
        }
    }
}