<?php

namespace Dan\Hooks\Types;

use Dan\Contracts\HookTypeContract;
use Illuminate\Support\Collection;

class HttpHook implements HookTypeContract
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var \anonymous
     */
    protected $class;

    /**
     * @var \Closure
     */
    protected $callable;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @param $uri
     *
     * @return $this
     */
    public function get($uri)
    {
        $this->method = 'get';
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param $uri
     *
     * @return $this
     */
    public function post($uri)
    {
        $this->method = 'post';
        $this->uri = $uri;

        return $this;
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
     * @param $args
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function run($args)
    {
        if ($args['path'] != $this->uri) {
            return false;
        }

        if ($args['path'] == $this->uri && $args['method'] != $this->method) {
            throw new \Exception("Method {$this->method} not allowed for uri {$this->uri}");
        }

        $args = new Collection($args);

        if ($this->class != null) {
            $class = $this->class;
            $method = isset($this->settings['method']) ? $this->settings['method'] : 'run';

            return $class->$method($args);
        }

        if ($this->callable != null) {
            $func = $this->callable;

            return $func($args);
        }

        return false;
    }
}
