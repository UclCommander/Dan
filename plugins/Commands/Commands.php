<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Plugins\Plugin;

class Commands extends Plugin implements PluginContract {

    protected $version      = '1.0';
    protected $author       = "UclCommander";
    protected $description  = "Dan's command plugin";

    /** @var CommandManager */
    protected $manager;


    /**
     * Registers the plugin.
     */
    public function register()
    {
        $this->manager = new CommandManager();

        $this->addEvent('irc.packet.privmsg', [$this->manager, 'checkForCommand'], 6);

    }

    /**
     * Unregisters the plugin.
     */
    public function unregister()
    {
        parent::unregister();
        $this->manager->destroy();
    }
}