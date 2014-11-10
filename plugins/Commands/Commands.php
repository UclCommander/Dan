<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Dan\Plugins\Plugin;

class Commands extends Plugin implements PluginContract {

    protected $version      = '1.0';
    protected $author       = "UclCommander";
    protected $description  = "Dan's command plugin";

    /**
     * @var CommandInterface[]
     */
    protected $commands = [];

    /**
     * Registers the plugin.
     */
    public function register()
    {
        $this->addEvent('irc.packet.privmsg', [$this, 'checkForCommand']);

        foreach(glob(PLUGIN_DIR.'/Commands/Command/*.php') as $cmd)
        {
            $command = basename($cmd, '.php');
            $className = ucfirst(strtolower($command));
            $class = "Plugins\\Commands\\Command\\{$className}";
            $this->commands[strtolower($command)] = new $class;
        }
    }

    /**
     * Unregisters the plugin.
     */
    public function unregister()
    {
        parent::unregister();
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

        $starter = Config::get('commands.command_starter');

        if(strpos($message, $starter) !== 0)
            return null;

        $data   = explode(' ', $message, 2);
        $cmd    = substr($data[0], 1);

        if(!array_key_exists($cmd, $this->commands) && Config::get('dan.show_nonexistent_command_error'))
        {
            $user->sendNotice("Command {$cmd} doesn't exist.");
            return false;
        }

        if(!$this->hasPermission($channel, $user, $cmd))
        {
            $user->sendNotice("You do not have the required permission to run this command.");
            return false;
        }

        $this->commands[$cmd]->run($channel, $user, @$data[1]);

        return false;
    }


    protected function hasPermission(Channel $channel, User $user, $command)
    {
        $ranks = Config::get('commands.ranks');


        if(!array_key_exists($command, $ranks))
            return false;
        //get the ranks required for the command
        $ranks      = str_split($ranks[$command]);
        $uranks     = $channel->getUser($user);


        if(in_array('S', $ranks))
        {
            foreach(Config::get('dan.sudo_users') as $usr)
                if(fnmatch($usr, "{$user->getNick()}!{$user->getName()}@{$user->getHost()}"))
                    return true;
        }

        return in_array($uranks, $ranks);
    }
}