<?php namespace Dan\Plugins; 

use Dan\Contracts\PluginContract;
use Dan\Events\Event;

abstract class Plugin implements PluginContract {

    protected $key          = null;

    protected $version      = null;
    protected $author       = null;
    protected $description  = null;

    /** @var Event[] $events */
    protected $events       = [];

    /**
     * Sets the plugin key.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        if($this->key == null)
            $this->key = $key;
    }

    /**
     * Gets the plugin key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Adds an event to the bucket.
     *
     * @param string $name
     * @param callable|array $function
     * @param int $priority
     */
    public function listenForEvent($name, $function, $priority = 5)
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
 