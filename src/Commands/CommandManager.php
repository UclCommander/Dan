<?php namespace Dan\Commands;


use Dan\Commands\Command\Blacklist as BlacklistCommand;
use Dan\Commands\Command\Config as ConfigCommand;
use Dan\Commands\Command\Plugin as PluginCommand;
use Dan\Contracts\CommandContract;
use Dan\Contracts\ServiceContract;
use Dan\Core\Config;
use Dan\Core\Dan;
use Dan\Events\Event;
use Dan\Events\EventArgs;
use Dan\Events\EventPriority;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;


class CommandManager implements ServiceContract {

    /** @var Collection */
    protected $commands;


    public function __construct()
    {
        $this->commands = new Collection();
    }

    /**
     * Adds a command.
     *
     * @param string $name
     * @param \Dan\Contracts\CommandContract $command
     */
    public function addCommand($name, CommandContract $command)
    {
        $this->commands->put($name, $command);
    }

    /**
     * Removes a command.
     *
     * @param $name
     */
    public function removeCommand($name)
    {
        $this->commands->forget($name);
    }

    /**
     * Registers the service.
     */
    public function register()
    {
        Dan::registerService('commands', $this);
        Event::subscribe('irc.packets.message.public', [$this, 'checkForCommand'], EventPriority::Critical);

        $this->addCommand('blacklist', new BlacklistCommand);
        $this->addCommand('config', new ConfigCommand);
        $this->addCommand('plugin', new PluginCommand);
    }

    /**
     * Not used.
     */
    public function unregister() {}

    /**
     * @param EventArgs $eventArgs
     */
    public function checkForCommand(EventArgs $eventArgs)
    {
        $config     = Config::get('commands');
        $message    = $eventArgs->get('message');

        /** @var \Dan\Irc\Location\Channel $user */
        $channel    = $eventArgs->get('channel');

        /** @var \Dan\Irc\Location\User $user */
        $user       = $eventArgs->get('user');


        if(strpos($message, $config->get('command_starter')) !== 0)
            return;

        if(Config::get('dan.blacklist_level') >= 1)
            if(Dan::blacklist()->check($user))
                return;

        $data = explode(' ', $message, 2);

        $command = substr($data[0], 1);

        if(empty($command))
            return;

        if($command == 'help')
        {
            array_shift($data);

            $this->handleHelp($user, $data);

            return;
        }

        if(!$this->commands->has($command))
        {
            if($config->get('no_command_error'))
                $user->sendNotice("Command '{$command}' does not exist");

            return;
        }

        if(!$this->hasPermission($command, $config, $user))
        {
            $user->sendNotice("You do not have the required permissions to use this command.");
            return;
        }

        $this->commands->get($command)->run($channel, $user, @$data[1]);
    }

    /**
     * Help command.
     *
     * @param \Dan\Irc\Location\User $user
     * @param array $data
     */
    private function handleHelp(User $user, array $data)
    {
        if(count($data) == 0)
        {
            $user->sendNotice(implode(', ', $this->commands->keys()));
            return;
        }

        $command = explode(' ', $data[0], 2);

        if(!$this->commands->has($command[0]))
        {
            $user->sendNotice("Command '{$command[0]}' does not exist");
            return;
        }

        $this->commands->get($command[0])->help($user, @$command[1]);
    }

    /**
     * Checks to see if a user has permission.
     *
     * @param string $command
     * @param Collection $config
     * @param User $user
     * @return bool
     */
    private function hasPermission($command, Collection $config, User $user)
    {
        $perms = $config->get('ranks')[$command];
        $ranks = str_split($perms);

        foreach(Config::get('dan.sudo_users') as $usr)
            if (fnmatch($usr, "{$user->getNick()}!{$user->getUser()}@{$user->getHost()}"))
                return true;

        foreach($ranks as $rank)
            if ($user->hasPrefix($rank))
                return true;

        return false;
    }

    /**
     * Initializes the manager
     */
    public static function init()
    {
        $self = new static;
        $self->register();
    }
}