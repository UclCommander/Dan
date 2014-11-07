<?php namespace Dan\Core;

use Dan\Irc\Connection;
use Dan\Plugins\PluginManager;

class Dan {

    const VERSION = '3.0.0';

    protected $irc;

    /** @var object[] */
    protected $apps = [];


    public function __construct()
    {
        session_start(); // Start sessions for flash data

        Config::load();
    }

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

        $this->irc = new Connection();
        $this->irc->init();
    }
}
 