<?php

namespace Dan\Commands;

use Dan\Console\Connection as ConsoleConnection;
use Dan\Console\User as ConsoleUser;
use Dan\Events\Event;
use Dan\Irc\Connection as IrcConnection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User as IrcUser;
use Illuminate\Support\Collection;

class CommandManager
{
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
        if (strpos($message, $connection->config->get('command_prefix')) === false) {
            return true;
        }

        $info = explode(' ', $message, 2);
        $name = substr($info[0], 1);

        if (!($command = $this->findCommand($name))) {
            console()->info("This command doesn't exist!");

            return false;
        }

        if (is_null($channel) && !$command->isUsableInPrivate()) {
            $connection->message($user, 'This command must be used in a channel.');

            return false;
        }

        $this->callCommand($command, [
            'connection'     => $connection,
            'user'           => $user,
            'message'        => $info[1] ?? null,
            'channel'        => $channel,
            'command'        => $name,
            'commandManager' => $this,
        ]);

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
     * Gets the required IRC connection if given.
     *
     * @param $param
     *
     * @return \Dan\Connection\Handler|\Dan\Contracts\ConnectionContract|bool
     */
    private function getIrcConnection(&$param)
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
}
