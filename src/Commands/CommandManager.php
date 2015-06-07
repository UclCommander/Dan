<?php namespace Dan\Commands; 

use Closure;
use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;
use SimilarText\Finder;

class CommandManager {

    protected $commands = [];


    /**
     *
     */
    public function __construct()
    {
        subscribe('irc.packets.message.public', [$this, 'checkForCommand'], EventPriority::VeryHigh);

        $this->commands = new Collection();
    }

    /**
     * @param $command
     * @param $func
     */
    public function registerCommand($command, $func)
    {
        debug("Registering command {$command}");
        $this->commands->put($command, $func);
    }

    /**
     * @param $command
     */
    public function unregisterCommand($command)
    {
        debug("Unregistering command {$command}");
        $this->commands->forget($command);
    }

    /**
     * Checks to see if the message is a command.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     * @return bool
     */
    public function checkForCommand(EventArgs $eventArgs)
    {
        $message    = $eventArgs->get('message');

        /** @var Channel $channel */
        $channel    = $eventArgs->get('channel');

        /** @var User $user */
        $user       = $eventArgs->get('user');

        if(strpos($message, config('commands.command_prefix')) !== 0)
            return null;

        $data       = explode(' ', $message, 2);
        $command    = strtolower(substr($data[0], 1));
        $args       = isset($data[1]) ? $data[1] : null;

        if(empty($command) || !ctype_alnum($command))
            return null;

        // Hacky override to allow 'help' access from command.
        if($args == 'help')
        {
            $args = $command;
            $command = 'help';
        }

        $continue = event('command.use', [
            'command'   => $command,
            'user'      => $user,
            'channel'   => $channel,
            'args'      => $args,
        ]);

        if($continue === false)
            return false;

        if(!$this->exists($command))
        {
            $finder = new Finder($command, array_keys($this->getCommands()));

            $possible   = $finder->first();
            $suggest    = '';

            if($this->exists($possible))
                $suggest = " Did you mean '{$finder->first()}'?";

            $channel->message("Command '{$command}' doesn't exist.{$suggest}");

            unset($finder);
            return false;
        }

        if($command == 'help')
        {
            controlLog("{$user->nick()} used '{$message}' in {$channel->getLocation()}");
            $this->help($channel, $user, $args);
            return false;
        }

        if(!$this->hasPermission($command, $user))
        {
            controlLog("{$user->string()} tried to use '{$message}' in {$channel->getLocation()}. Rank: " . $user->modes()->implode(''));
            $channel->message("You do not have the required permissions to use this command.");
            return false;
        }

        controlLog("{$user->nick()} used '{$message}' in {$channel->getLocation()}");

        $this->runCommand($command, 'use', $channel, $user, $args);

        return false;
    }

    /**
     * Checks to see if a command exists.
     *
     * @param $command
     * @return bool
     */
    public function exists($command)
    {
        if($command == 'help')
            return true;

        if($this->commands->has($command))
            return true;

        return filesystem()->exists(COMMAND_DIR . '/' . $command . '.php');
    }

    /**
     * Gets all commands.
     *
     * @return array
     */
    public function getCommands()
    {
        $commands = [];
        $commands['help'] = 'help';

        foreach(filesystem()->files(COMMAND_DIR) as $file)
            $commands[strtolower(basename($file, '.php'))] = basename($file);

        foreach($this->commands->toArray() as $command => $class)
            $commands[$command] = $command;

        return $commands;
    }

    /**
     * Checks to see if a user has permission to use a command.
     *
     * @param $command
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    private function hasPermission($command, User $user)
    {
        if(Dan::isOwner($user))
            return true;

        $rank = config("commands.commands.{$command}");

        if($rank == null)
            $rank = config('commands.default_permissions');

        if(in_array('A', str_split($rank), true))
            if(Dan::isAdmin($user))
                return true;

        return $user->hasOneOf($rank);
    }

    //
    //
    //

    /**
     * Runs a command.
     *
     * @param $command
     * @param $entry
     * @param \Dan\Irc\Location\Location $channel
     * @param \Dan\Irc\Location\User $user
     * @param null $message
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function runCommand($command, $entry, Location $channel, User $user, $message = null)
    {
        if($this->commands->has($command))
            return $this->runPluginCommand($command, $entry, $channel, $user, $message);

        return $this->runFileCommand($command, $entry, $channel, $user, $message);
    }

    /**
     * @param $command
     * @param $entry
     * @param \Dan\Irc\Location\Location $channel
     * @param \Dan\Irc\Location\User $user
     * @param null $message
     * @return mixed
     */
    protected function runFileCommand($command, $entry, Location $channel, User $user, $message = null)
    {
        $location = $channel;

        return include(COMMAND_DIR . '/' . $command . '.php');
    }


    /**
     * @param $command
     * @param $entry
     * @param \Dan\Irc\Location\Location $channel
     * @param \Dan\Irc\Location\User $user
     * @param null $message
     * @return null
     */
    protected function runPluginCommand($command, $entry, Location $channel, User $user, $message = null)
    {
        $command = $this->commands->get($command);

        if($command instanceof Command)
        {
            if($entry == 'use')
                return $command->run($channel, $user, $message);

            if($entry == 'help')
                return $command->help($user, $message);
        }

        if($command instanceof Closure)
        {
            return $command($entry, $channel, $user, $message);
        }

        if(is_array($command))
        {
            return call_user_func_array($command, [$entry, $channel, $user, $message]);
        }

        return null;
    }


    /**
     * Runs the help command.
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param $message
     */
    protected function help(Channel $channel, User $user, $message)
    {
        if(empty($message))
        {
            $commands = array_keys($this->getCommands());

            sort($commands);

            $event = event('command.help.messages', [
                'channel'   => $channel,
                'user'      => $user,
                'messages'  => [implode(', ', $commands)]
            ]);

            if($event === false)
                return;

            $user->notice(implode(', ', $commands));
            return;
        }

        $data = $this->runCommand($message, 'help', $channel, $user);

        $event = event('command.help.messages', [
            'channel'   => $channel,
            'user'      => $user,
            'messages'  => (array)$data
        ]);

        if($event === false)
            return;

        foreach((array)$data as $line)
            notice($user, str_replace('{cp}', config('commands.command_prefix'), $line));
    }

}