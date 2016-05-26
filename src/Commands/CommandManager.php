<?php

namespace Dan\Commands;

use Dan\Commands\Traits\RateLimiter;
use Dan\Console\Connection as ConsoleConnection;
use Dan\Console\User as ConsoleUser;
use Dan\Events\Event;
use Dan\Irc\Connection;
use Dan\Irc\Connection as IrcConnection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User as IrcUser;
use Illuminate\Support\Collection;
use SimilarText\Finder;

class CommandManager
{
    use RateLimiter;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $commands;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $aliases;

    public function __construct()
    {
        events()->subscribe('addons.load', function () {
            $this->commands = new Collection();
            $this->aliases = new Collection();
        }, Event::VeryHigh);

        events()->subscribe('irc.message.public', [$this, 'handleCommand'], Event::VeryHigh);
        events()->subscribe('irc.message.private', [$this, 'handlePrivateCommand'], Event::High);
        events()->subscribe('console.message', [$this, 'handleConsoleCommand'], Event::VeryHigh);
    }

    /**
     * @return Collection
     */
    public function commands() : Collection
    {
        return $this->commands;
    }

    /**
     * @param array $aliases
     *
     * @return \Dan\Commands\Command
     */
    public function registerCommand(array $aliases) : Command
    {
        console()->info("Loading command {$aliases[0]}");
        $command = new Command($aliases);

        $name = $aliases[0];

        $this->commands->put($name, $command);

        foreach ($aliases as $alias) {
            $this->aliases->put($alias, $name);
        }

        return $command;
    }

    /**
     * @param \Dan\Irc\Connection    $connection
     * @param \Dan\Irc\Location\User $user
     * @param $message
     *
     * @return bool
     */
    public function handlePrivateCommand(IrcConnection $connection, IrcUser $user, $message) : bool
    {
        return $this->handleCommand($connection, $user, $message, null);
    }

    /**
     * @param \Dan\Irc\Connection       $connection
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User    $user
     * @param $message
     *
     * @throws \Exception
     *
     * @return bool|void
     */
    public function handleCommand(IrcConnection $connection, IrcUser $user, $message, Channel $channel = null) : bool
    {
        $location = $channel ?? $user;

        if (strpos($message, $connection->config->get('command_prefix')) !== 0) {
            return true;
        }

        list($name, $info) = $this->getCommandName($message);

        if (preg_match('/[^a-zA-Z0-9:]/', $name) > 0) {
            return true;
        }

        $eventData = [
            'connection'     => $connection,
            'user'           => $user,
            'message'        => $info ?? null,
            'channel'        => $channel,
            'command'        => $name,
            'commandManager' => $this,
        ];

        if ($this->isChannelLocked($channel, $eventData) && !$connection->isAdminOrOwner($user)) {
            return false;
        }

        if (!($command = $this->findCommand($name))) {
            if (events()->fire('command.not_found', $eventData) === false) {
                return false;
            }

            if ($connection->config->get('command_not_found_error', true)) {
                $commands = $this->aliases->keys()->filter(function ($cmd) use ($connection, $user) {
                    return $this->canUseCommand($connection, $this->findCommand($cmd), $user);
                })->toArray();

                $similar = new Finder($name, $commands);

                $location->message("The command <i>{$name}</i> doesn't exist. Did you mean <i>{$similar->first()}</i>?");
            }

            return false;
        }

        if ($this->isCommandDisabled($name, $channel, $eventData) && !$connection->isAdminOrOwner($user)) {
            console()->debug("Ignoring command {$name} from {$channel} because it's set to be ignored.");

            return false;
        }

        if (!$this->canUseCommand($connection, $command, $user) && events()->fire('command.permission.check', $eventData) !== false) {
            if (events()->fire('command.permission.error', $eventData) !== false) {
                $location->message("You don't have the permissions to use this command.");
            }

            $permission = implode('', $user->modes());
            $netChan = "{$connection->getName()}:{$channel->getLocation()}";
            controlLog("[ <red>Permission Error</red> ] <cyan>{$user->nick}</cyan> attempted to use the command <i>{$name}</i> in {$netChan} - Permissions: +{$permission}");

            return false;
        }

        if (is_null($channel) && !$command->isUsableInPrivate()) {
            $location->message('This command must be used in a channel.');

            return false;
        }

        if ($this->checkRate($connection, $user, $command) && events()->fire('command.rate.exceeded', $eventData) !== false) {
            controlLog("[ <red>Rate Limiter</red> ] <cyan>{$user->nick}</cyan> attempted to use the command <i>{$name}</i> - Rate Limit exceeded.");

            if ($this->isSpamming($user) && $channel->getUser($connection->user)->hasPermissionTo('kick')) {
                $channel->kick($user, 'Stop spamming commands!');
            }

            return false;
        }

        $ran = $this->callCommand($command, $eventData);

        if ($ran === false) {
            $this->callCommand($this->findCommand('help'), [
                'connection'     => $connection,
                'user'           => $user,
                'message'        => $name,
                'channel'        => $channel,
                'command'        => 'help',
                'commandManager' => $this,
            ]);
        }

        $this->addRate($user, $command);

        controlLog("[ <red>Command Log</red> ] <cyan>{$user->nick}</cyan> used the command <i>{$name}</i> in {$connection->getName()}:{$channel->getLocation()}");

        return false;
    }

    /**
     * @param \Dan\Console\Connection $connection
     * @param $message
     *
     * @return bool
     */
    public function handleConsoleCommand(ConsoleConnection $connection, $message) : bool
    {
        if (strpos($message, '/') === false) {
            return true;
        }

        $info = explode(' ', $message, 2);
        $name = substr($info[0], 1);
        $param = $info[1] ?? null;

        if (!($command = $this->findCommand($name))) {
            console()->info("This command doesn't exist!");

            return false;
        }

        if (!$command->isUsableInConsole()) {
            console()->info('This command cannot be used in the console.');

            return false;
        }

        $irc = null;

        if ($command->begsForIrcConnection()) {
            if (($irc = $this->getIrcConnection($param)) === false) {
                console()->info('This command requires an IRC connection. <yellow>/command <red>:ircname</red> arguments</yellow> to specify one.');

                return false;
            }
        }

        $this->callCommand($command, [
            'connection'     => $irc,
            'user'           => new ConsoleUser(),
            'message'        => $param,
            'channel'        => null,
            'command'        => $name,
            'commandManager' => $this,
        ]);

        return false;
    }

    /**
     * @param $name
     *
     * @return bool|Command
     */
    public function findCommand($name)
    {
        if (!$this->aliases->has($name)) {
            return false;
        }

        return $this->commands->get($this->aliases->get($name));
    }

    /**
     * @param \Dan\Commands\Command $command
     * @param $args
     *
     * @return mixed
     */
    public function callCommand(Command $command, $args)
    {
        if (events()->fire('command.call', $args) === false) {
            return false;
        }

        $handler = $command->getHandler();
        $func = $handler;

        if (!($handler instanceof \Closure)) {
            $func = [$handler, 'run'];
        }

        try {
            return dan()->call($func, $args);
        } catch (\Error $error) {
            console()->exception($error);
        } catch (\Exception $exception) {
            console()->exception($exception);
        }

        return null;
    }

    /**
     * Checks to see if a user can run a command.
     *
     * @param \Dan\Irc\Connection $connection
     * @param \Dan\Commands\Command $command
     * @param \Dan\Irc\Location\User $user
     *
     * @return bool
     */
    public function canUseCommand(Connection $connection, Command $command, IrcUser $user)
    {
        foreach (str_split($command->getRank()) as $rank) {
            if (in_array($rank, ['x', '*'])) {
                return true;
            }
        }

        if ($connection->isOwner($user)) {
            return true;
        }

        if ($connection->isAdmin($user) && strpos($command->getRank(), 'S') !== false) {
            return true;
        }

        return $user->hasOneOf($command->getRank());
    }

    /**
     * Gets the required IRC connection if given.
     *
     * @param $param
     *
     * @return \Dan\Connection\Handler|\Dan\Contracts\ConnectionContract|bool
     */
    protected function getIrcConnection(&$param)
    {
        if (strpos($param, ':') === false) {
            return false;
        }

        $data = explode(' ', $param, 2);
        $conn = substr($data[0], 1);
        $param = $data[1] ?? null;

        if (connection()->hasConnection($conn)) {
            return connection($conn);
        }

        return true;
    }

    /**
     * @param $message
     *
     * @return array|bool
     */
    protected function getCommandName($message)
    {
        $clean = substr($message, 1);

        if (empty($clean)) {
            return true;
        }

        return explode(' ', $clean, 2);
    }

    /**
     * @param \Dan\Irc\Location\Channel $channel
     * @param array $eventData
     *
     * @return bool
     */
    protected function isChannelLocked(Channel $channel, array $eventData)
    {
        if ($channel == null) {
            return false;
        }

        if (events()->fire('command.channel.locked', $eventData) === false) {
            return false;
        }

        return $channel->getData('locked', false);
    }

    /**
     * @param $name
     * @param \Dan\Irc\Location\Channel $channel
     * @param array $eventData
     *
     * @return bool
     */
    protected function isCommandDisabled($name, Channel $channel, array $eventData)
    {
        if ($channel == null) {
            return false;
        }

        if (events()->fire('command.channel.ignored', $eventData) === false) {
            return false;
        }

        if (in_array($name, $channel->getData('info.commands.disabled', []))) {
            return true;
        }

        return false;
    }
}
