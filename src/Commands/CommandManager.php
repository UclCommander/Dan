<?php namespace Dan\Commands; 

use Dan\Core\Dan;
use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class CommandManager {

    /**
     *
     */
    public function __construct()
    {
        subscribe('irc.packets.message.public', [$this, 'checkForCommand']);
    }

    /**
     * Checks to see if the message is a command.
     *
     * @param \Dan\Events\EventArgs $eventArgs
     */
    public function checkForCommand(EventArgs $eventArgs)
    {
        $message    = $eventArgs->get('message');

        /** @var Channel $channel */
        $channel    = $eventArgs->get('channel');

        /** @var User $user */
        $user       = $eventArgs->get('user');

        if(strpos($message, config('commands.command_starter')) !== 0)
            return;

        $data       = explode(' ', $message, 2);
        $command    = strtolower(substr($data[0], 1));

        event('command.use', [
            'command'   => $command,
            ''
        ]);

        if(!$this->exists($command))
        {
            $channel->message("Command {$command} doesn't exist.");
            return;
        }

        if($command == 'help')
        {
            controlLog("{$user->nick()} used '{$message}' in {$channel->getLocation()}");
            $this->help($channel, $user, @$data[1]);
            return;
        }


        if(!$this->hasPermission($command, $user))
        {
            controlLog("{$user->nick()} tried to use '{$message}'
             in {$channel->getLocation()}");
            $channel->message("You do not have the required permissions to use this command.");
            return;
        }

        controlLog("{$user->nick()} used '{$message}' in {$channel->getLocation()}");

        $this->runCommand($command, 'use', $channel, $user, @$data[1]);
    }

    /**
     * Runs a command.
     *
     * @param $command
     * @param $entry
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param null $message
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function runCommand($command, $entry, Channel $channel, User $user, $message = null)
    {
        return include(COMMAND_DIR . '/' . $command . '.php');
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

        foreach(filesystem()->files(COMMAND_DIR) as $file)
            $commands[strtolower(basename($file, '.php'))] = basename($file);

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
            $user->notice(implode(', ', array_keys($this->getCommands())));
            return;
        }

        $data = $this->runCommand($message, 'help', $channel, $user);

        foreach((array)$data as $line)
            notice($user, $line);
    }
}