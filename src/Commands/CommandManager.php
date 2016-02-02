<?php

namespace Dan\Commands;

use Dan\Events\Event;
use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;
use Illuminate\Support\Collection;

class CommandManager
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $commands = [];

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $aliases = [];

    public function __construct()
    {
        $this->commands = new Collection();
        $this->aliases = new Collection();

        events()->subscribe('addons.load', function () {
            $this->commands = new Collection();
            $this->aliases = new Collection();
        }, Event::VeryHigh);

        events()->subscribe('irc.message.public', [$this, 'handleCommand'], Event::VeryHigh);
        events()->subscribe('irc.message.private', [$this, 'handlePrivateCommand'], Event::High);
        // TODO: Console watching.
        // events()->subscribe('console.message', [$this, 'handleConsoleCommand'], Event::High);
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

        $this->commands->put($aliases[0], $command);

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
    public function handlePrivateCommand(Connection $connection, User $user, $message) : bool
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
    public function handleCommand(Connection $connection, User $user, $message, Channel $channel = null) : bool
    {
        if (strpos($message, $connection->config->get('command_prefix')) === false) {
            return true;
        }

        $info = explode(' ', $message, 2);
        $name = substr($info[0], 1);

        if (!$this->aliases->has($name)) {
            $connection->message($channel ?? $user, "This command doesn't exist!");

            return false;
        }

        /** @var Command $command */
        $command = $this->commands->get($this->aliases->get($name));
        $handler = $command->getHandler();
        $func = $handler;

        if (is_null($channel) && !$command->isUsableInPrivate()) {
            $connection->message($user, 'This command must be used in a channel.');

            return false;
        }

        if (!($handler instanceof \Closure)) {
            $func = [$handler, 'run'];
        }

        dan()->call($func, [
            'connection'     => $connection,
            'user'           => $user,
            'message'        => $info[1] ?? null,
            'channel'        => $channel,
            'command'        => $name,
            'commandManager' => $this,
        ]);

        return false;
    }
}
