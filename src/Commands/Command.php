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

    /**
     * @var bool
     */
    protected $requiresIrcConnection = false;

    /**
     * @var string
     */
    protected $rank;

    /**
     * Command constructor.
     *
     * @param $aliases
     */
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
     * Only applied when usableInConsole is set to true.
     *
     * @param bool $bool
     *
     * @return \Dan\Commands\Command
     */
    public function requiresIrcConnection($bool = true) : Command
    {
        $this->requiresIrcConnection = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return \Dan\Commands\Command
     */
    public function allowConsole($bool = true) : Command
    {
        $this->canBeUsedInConsole = $bool;

        return $this;
    }

    /**
     * @param bool $bool
     *
     * @return \Dan\Commands\Command
     */
    public function allowPrivate($bool = true) : Command
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
    public function begsForIrcConnection() : bool
    {
        return $this->requiresIrcConnection;
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
     * Get command aliases.
     *
     * @return array
     */
    public function getAliases() : array
    {
        return $this->aliases;
    }

    /**
     * Gets the commands help text.
     *
     * @return array
     */
    public function getHelpText() : array
    {
        return $this->helpText;
    }

    /**
     * Gets the command rank.
     *
     * @return string
     */
    public function getRank()
    {
        return $this->rank;
    }
}
