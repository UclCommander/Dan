<?php namespace Dan\Plugins; 

use Dan\Contracts\PluginContract;

abstract class Plugin implements PluginContract {

    protected $version      = null;
    protected $author       = null;
    protected $description  = null;

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
 