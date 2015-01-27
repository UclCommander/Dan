<?php namespace Dan\Commands;


use Dan\Contracts\CommandContract;

abstract class Command implements CommandContract {

    protected $defaultRank = 'x+%@&~';

    /**
     * Returns the default rank.
     *
     * @return string
     */
    public function getDefaultRank()
    {
        return $this->defaultRank;
    }

    /**
     * Gets the commands name.
     *
     * @return string
     */
    public function getName()
    {
        return strtolower(get_called_class());
    }
}