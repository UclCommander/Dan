<?php namespace Dan\Core;

use Dan\Irc\Connection;
use Dan\Plugins\PluginManager;
use Illuminate\Support\Collection;

class Dan {

    const VERSION = '3.0.0';

    /** @var object[] */
    protected $apps = [];

    /** @var static */
    protected static $dan;

    /**
     * Load 'er up.
     */
    public function __construct()
    {
        session_start(); // Start sessions for flash data

        static::$dan    = $this;
        $this->apps     = new Collection();

        Config::load();
    }

    /**
     * Boots Dan.
     */
    public function boot()
    {
        Console::text('Booting Dan...')->info()->push();

        if(Config::get('dan.debug'))
        {
            error_reporting(E_ALL);
            ini_set("display_errors", true);
            Console::text("Debug mode is active!")->debug()->alert()->push();
        }

        $this->apps->put('pluginManager', new PluginManager());

        try
        {
            foreach(Config::get('dan.plugins') as $plugin)
                $this->apps->get('pluginManager')->loadPlugin($plugin);
        }
        catch (\Exception $e)
        {
            Console::exception($e)->push();
        }

        Console::text('System Booted. Starting IRC connection. ')->alert()->push();

        $this->apps->put('irc', new Connection());
        $this->apps->get('irc')->init();
    }

    /**
     * Gets an app or the application collection.
     *
     * @param $key
     * @return object|Collection
     */
    public static function app($key = null)
    {
        if($key == null)
            return static::$dan->apps;

        return static::$dan->apps->get($key);
    }
}
 