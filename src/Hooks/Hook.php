<?php

namespace Dan\Hooks;

use Dan\Contracts\HookTypeContract;
use Dan\Hooks\Types\CommandHook;
use Dan\Hooks\Types\EventHook;
use Dan\Hooks\Types\HttpHook;
use Dan\Hooks\Types\RegexHook;

class Hook
{
    /**
     * @var HookTypeContract
     */
    protected $hook;

    /**
     * @var string
     */
    protected $type = false;

    /**
     * @var
     */
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     *
     * @return \Dan\Hooks\Types\CommandHook
     */
    public function command($name) : CommandHook
    {
        $this->type = 'command';
        $this->hook = new CommandHook((array) $name);

        return $this->hook;
    }

    /**
     * Creates a command hook.
     *
     * @param $event
     * @param array $settings
     *
     * @return \Dan\Hooks\Types\EventHook
     */
    public function on($event, array $settings = []) : EventHook
    {
        $this->type = 'event';
        $this->hook = new EventHook($this->name, (array) $event, $settings);

        return $this->hook;
    }

    /**
     * Creates a command hook.
     *
     * @param $regex
     * @param array $settings
     *
     * @return \Dan\Hooks\Types\RegexHook
     */
    public function regex($regex, array $settings = []) : RegexHook
    {
        $this->type = 'regex';
        $this->hook = new RegexHook($regex, $settings);

        return $this->hook;
    }

    /**
     * Creates a HTTP hook.
     *
     * @param array $settings
     *
     * @return \Dan\Hooks\Types\HttpHook
     */
    public function http(array $settings = []) : HttpHook
    {
        $this->type = 'http';
        $this->hook = new HttpHook($settings);

        return $this->hook;
    }
}
