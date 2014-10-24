<?php namespace Dan\Core;


class Dan {

    const VERSION = '3.0.0';

    protected $config;

    public function __construct()
    {
        session_start(); // Start sessions for flash data

        $this->config = Config::load();
    }

    public function boot()
    {
        Console::text('Booting Dan...')->alert()->push();



        Console::text('System Booted.')->alert()->push();
    }
}
 