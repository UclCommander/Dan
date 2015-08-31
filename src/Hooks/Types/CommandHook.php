<?php namespace Dan\Hooks\Types;


use Dan\Contracts\HookTypeContract;
use Illuminate\Support\Collection;

class CommandHook implements HookTypeContract {

    /**
     * @var array
     */
    public $commands = [];

    /**
     * @var bool
     */
    public $canRunInConsole = false;

    /**
     * @var string
     */
    public $rank = null;

    /**
     * @var string
     */
    public $help = null;

    /**
     * @var object
     */
    protected $class;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @param array $names
     */
    public function __construct($names = [])
    {
        $this->commands = $names;
    }

    /**
     * @return \Dan\Hooks\Types\CommandHook
     */
    public function console() : CommandHook
    {
        $this->canRunInConsole = true;
        return $this;
    }

    /**
     * @param $rank
     * @return \Dan\Hooks\Types\CommandHook
     */
    public function rank($rank) : CommandHook
    {
        $this->rank = $rank;
        return $this;
    }

    /**
     * @param $text
     * @return \Dan\Hooks\Types\CommandHook
     */
    public function help($text) : CommandHook
    {
        $this->help = $text;
        return $this;
    }

    /**
     * @param callable $callable
     */
    public function func(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param $anonymous
     */
    public function anon($anonymous)
    {
        $this->class = $anonymous;
    }


    /**
     * @param $args
     */
    public function run($args)
    {
        if($this->callable != null)
        {
            $func = $this->callable;

            $func(new Collection($args));
        }
    }
}