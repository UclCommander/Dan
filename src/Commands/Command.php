<?php

namespace Dan\Commands;

class Command
{
    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var bool
     */
    protected $canBeUsedInConsole = false;

    /**
     * @var bool
     */
    protected $canBeUsedInPrivate = false;

    /**
     * @var array
     */
    protected $helpText = [];

    /**
     * @var callable
     */
    protected $handler;

    public function __construct($aliases)
    {
        $this->aliases = (array) $aliases;
    }

    /**
     * @param $rank
     *
     * @return Command
     */
    public function rank($rank) : Command
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return \Dan\Commands\Command
     */
    public function usableInConsole($bool = true) : Command
    {
        $this->canBeUsedInConsole = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return \Dan\Commands\Command
     */
    public function usableInPrivate($bool = true) : Command
    {
        $this->canBeUsedInPrivate = $bool;

        return $this;
    }

    /**
     * @param array $helpText
     *
     * @return Command
     */
    public function helpText($helpText) : Command
    {
        $this->helpText = (array) $helpText;

        return $this;
    }

    /**
     * @param $handler
     */
    public function handler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return bool
     */
    public function isUsableInConsole() : bool
    {
        return $this->canBeUsedInConsole;
    }

    /**
     * @return bool
     */
    public function isUsableInPrivate() : bool
    {
        return $this->canBeUsedInPrivate;
    }

    /**
     * @return array
     */
    public function getAliases() : array
    {
        return $this->aliases;
    }

    /**
     * @return array
     */
    public function getHelpText() : array
    {
        return $this->helpText;
    }
}
