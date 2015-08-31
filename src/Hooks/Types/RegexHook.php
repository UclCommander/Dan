<?php namespace Dan\Hooks\Types;

use Dan\Contracts\HookTypeContract;

class RegexHook implements HookTypeContract {

    /**
     * @var object
     */
    protected $class;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var
     */
    protected $regex;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param $regex
     * @param array $settings
     */
    public function __construct($regex, array $settings)
    {
        $this->regex = $regex;
        $this->settings = $settings;
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
     * @param $args
     * @return bool
     */
    public function run($args)
    {
        if(!preg_match($this->regex, $args['message'], $matches))
            return false;

        $args['matches'] = $matches;

        if($this->class != null)
        {
            $class = $this->class;
            $method = isset($this->settings['method']) ? $this->settings['method'] : 'run';

            return $class->$method($args);
        }
    }
}