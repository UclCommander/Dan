<?php namespace Dan\Hooks;


use Dan\Contracts\HookTypeContract;
use Dan\Hooks\Types\CommandHook;
use Dan\Hooks\Types\EventHook;
use Dan\Hooks\Types\RegexHook;

class Hook {

    /**
     * @var HookTypeContract
     */
    protected $hook;

    /**
     * @var string
     */
    protected $type = false;

    /**
     * Returns of the hook is a command or not.
     *
     * @return bool
     */
    public function isCommand()
    {
        return $this->type == 'command';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Dan\Contracts\HookTypeContract|EventHook|CommandHook|RegexHook
     */
    public function hook() : HookTypeContract
    {
        return $this->hook;
    }

    /**
     * Creates a command hook.
     *
     * @param $name
     * @return \Dan\Hooks\Types\CommandHook
     */
    public function command($name) : CommandHook
    {
        $this->type = 'command';
        $this->hook = new CommandHook((array)$name);
        return $this->hook;
    }

    /**
     * Creates a command hook.
     *
     * @param $event
     * @return \Dan\Hooks\Types\EventHook
     */
    public function on($event) : EventHook
    {
        $this->type = 'event';
        $this->hook = new EventHook($event);
        return $this->hook;
    }

    /**
     * Creates a command hook.
     *
     * @param $regex
     * @param array $settings
     * @return \Dan\Hooks\Types\RegexHook
     */
    public function regex($regex, array $settings = []) : RegexHook
    {
        $this->type = 'regex';
        $this->hook = new RegexHook($regex, $settings);
        return $this->hook;
    }
}