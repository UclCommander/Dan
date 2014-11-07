<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Plugins\Plugin;

class Commands extends Plugin implements PluginContract {

    public function register()
    {
        Console::text('PLUGIN LOADED')->debug()->success()->push();
        Event::listen('irc.packet.privmsg', [$this, 'checkForCommand']);
    }


    public function checkForCommand($event)
    {
        var_dump($event);
    }
}