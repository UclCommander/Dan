<?php namespace Dan\Plugins; 

use Dan\Contracts\PluginContract;
use Dan\Events\Event;

abstract class Plugin implements PluginContract {

    protected $version      = null;
    protected $author       = null;
    protected $description  = null;

    /** @var Event[] $events */
    protected $events       = [];


    /**
     * Adds an event to the bucket.
     *
     * @param     $name
     * @param     $function
     * @param int $priority
     */
    public function addEvent($name, $function, $priority = 5)
    {
        $this->events[] = Event::listen($name, $function, $priority);
    }

    /**
     * Unregisters the plugin.
     */
    public function unregister()
    {
        foreach ($this->events as $e)
            $e->destroy();
    }

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
 