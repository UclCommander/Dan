<?php namespace Dan\Core;

use Dan\Irc\Connection;
use Dan\Plugins\PluginManager;

class Dan {

    const VERSION = '3.0.0';

    protected $irc;

    /** @var object[] */
    protected $apps = [];

    protected static $dan;

    /**
     * Gets an app
     *
     * @param $key
     * @return object
     */
    public static function getApp($key)
    {
        return static::$dan->apps[$key];
    }

    /**
     * Load 'er up.
     */
    public function __construct()
    {
        session_start(); // Start sessions for flash data

        static::$dan = $this;

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

        $this->apps['pluginManager'] = new PluginManager();

        try
        {
            $this->apps['pluginManager']->loadPlugin("commands");
        }
        catch (\Exception $e)
        {
            Console::exception($e)->push();
        }

        Console::text('System Booted. Starting IRC connection. ')->alert()->push();

        $this->apps['irc'] = new Connection();
        $this->apps['irc']->init();
    }
}
 