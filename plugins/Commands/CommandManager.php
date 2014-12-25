<?php namespace Plugins\Commands; 


use Dan\Core\Config;
use Dan\Core\Console;
use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Irc\Channel;
use Dan\Irc\User;

class CommandManager {

    /**
     * @var CommandInterface[]
     */
    protected $commands = [];

    /**
     * Registers the CommandManager app reference.
     */
    public function __construct()
    {
        Dan::app()->put('commandManager', $this);

        $this->load();
    }

    /**
     * Destroys the CommandManager app reference.
     */
    public function destroy()
    {
        Dan::app()->forget('commandManager');
    }

    /**
     * Loads all system commands.
     */
    public function load()
    {
        foreach(glob(PLUGIN_DIR.'/Commands/Command/*.php') as $cmd)
        {
            $command = basename($cmd, '.php');
            $className = ucfirst(strtolower($command));
            $class = "Plugins\\Commands\\Command\\{$className}";

            $this->register($command, new $class);
        }
    }

    /**
     * Registers a command.
     *
     * @param $name
     * @param $class
     */
    public function register($name, $class)
    {
        $this->commands[strtolower($name)] = $class;
    }

    /**
     * Un-registers a command.
     *
     * @param $name

     */
    public function unregister($name)
    {
        unset($this->commands[strtolower($name)]);
    }

    /**
     * Checks for a command and runs it.
     *
     * @param $event
     * @return bool
     */
    public function checkForCommand(EventArgs $event)
    {
        /** @var \Dan\Irc\Channel $channel */
        $channel = $event->channel;
        $message = $event->message;

        /** @var \Dan\Irc\User $user */
        $user    = $event->user;

        $starter = Config::get('commands.command_starter');

        if(strpos($message, $starter) !== 0)
            return null;

        $data   = explode(' ', $message, 2);
        $cmd    = substr($data[0], 1);

        if(empty($cmd))
        {
            Console::text("Command empty")->debug()->info()->push();
            return null;
        }

        if($cmd == 'help')
        {
            $theHelp = explode(' ', @$data[1], 2);

            if(empty($theHelp[0]))
            {
                $user->sendNotice(implode(', ', array_keys($this->commands)));
            }
            else if(!$this->exists($theHelp[0]))
            {
                $user->sendNotice("Command {$theHelp[0]} doesn't exist.");
            }
            else
            {
                $this->commands[$theHelp[0]]->help($user, @$theHelp[1]);
            }
        }
        else if(!$this->exists($cmd))
        {
            if(Config::get('commands.show_nonexistent_command_error'))
                $user->sendNotice("Command {$cmd} doesn't exist.");
        }
        else if(!$this->hasPermission($channel, $user, $cmd))
        {
            $user->sendNotice("You do not have the required permission to run this command.");
        }
        else
        {
            $this->commands[$cmd]->run($channel, $user, @$data[1]);
        }

        return false;
    }

    /**
     * Checks to see if the command exists.
     *
     * @param $cmd
     * @return bool
     */
    protected function exists($cmd)
    {
        return array_key_exists($cmd, $this->commands);
    }

    /**
     * Checks to see if a user has the permission to use a command.
     *
     * @param \Dan\Irc\Channel $channel
     * @param \Dan\Irc\User    $user
     * @param                  $command
     * @return bool
     */
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