<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Events\Event;
use Dan\Plugins\Plugin;

class Commands extends Plugin implements PluginContract {

    /**
     * @var CommandInterface[]
     */
    protected $commands = [];

    /**
     * Registers the plugin.
     */
    public function register()
    {
        Console::text('PLUGIN LOADED')->debug()->success()->push();
        Event::listen('irc.packet.privmsg', [$this, 'checkForCommand']);

        foreach(glob(PLUGIN_DIR.'/Commands/Command/*.php') as $cmd)
        {
            $command = basename($cmd, '.php');
            $className = ucfirst(strtolower($command));
            $class = "Plugins\\Commands\\Command\\{$className}";
            $this->commands[strtolower($command)] = new $class;
        }
    }

    /**
     * Checks for a command and runs it.
     *
     * @param $event
     * @return bool
     */
    public function checkForCommand($event)
    {
        /** @var \Dan\Irc\Channel $channel */
        $channel = $event[0];
        $message = $event[1];

        /** @var \Dan\Irc\User $user */
        $user    = $event[2];

        $starter = Config::get('dan.command_starter');

        if(strpos($message, $starter) !== 0)
            return null;

        $data   = explode(' ', $message);
        $cmd    = substr($data[0], 1);

        if(!array_key_exists($cmd, $this->commands) && Config::get('dan.show_nonexistent_command_error'))
        {
            $user->sendNotice("Command {$cmd} doesn't exist.");
            return false;
        }

        $this->commands[$cmd]->run($channel, $user, @$data[1]);

        return false;
    }
}