<?php namespace Dan\Core;


use Dan\Irc\Connection;

class Dan {

    const VERSION = '3.0.0';

    protected $irc;

    public function __construct()
    {
        session_start(); // Start sessions for flash data

        Config::load();
    }

    public function boot()
    {
        Console::text('Booting Dan...')->alert()->push();

        Console::text('System Booted. Starting IRC connection. ')->alert()->push();

        $this->irc = new Connection();
        $this->irc->init();
    }
}
 