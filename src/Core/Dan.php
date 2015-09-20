<?php namespace Dan\Core;

use Dan\Console\Console;
use Dan\Contracts\MessagingContract;
use Dan\Contracts\SocketContract;
use Dan\Database\Database;
use Dan\Database\DatabaseManager;
use Dan\Events\EventArgs;
use Dan\Helpers\Logger;
use Dan\Hooks\HookManager;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Setup\Setup;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Dan {

    /** @var string Current version */
    const VERSION = '5.0.0-dev';

    /** @var array */
    protected static $args = [];

    /** @var string */
    protected static $currentConnection;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var SocketContract[] */
    protected $connections = [];

    /** @var static $dan */
    protected static $dan;

    /** @var DatabaseManager */
    protected $databaseManager;

    /** @var bool $running */
    protected $running = false;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        static::$dan    = $this;

        $this->filesystem       = new Filesystem();
        $this->databaseManager  = new DatabaseManager();

        if(!filesystem()->exists(CONFIG_DIR))
            filesystem()->makeDirectory(CONFIG_DIR);

        if(!filesystem()->exists(STORAGE_DIR))
            filesystem()->makeDirectory(STORAGE_DIR);

        if(!filesystem()->exists(DATABASE_DIR))
            filesystem()->makeDirectory(DATABASE_DIR);

        if(!filesystem()->exists(HOOK_DIR))
            filesystem()->makeDirectory(HOOK_DIR);
    }

    /**
     * Boots up Dan.
     */
    public function boot()
    {
        define('DEBUG', $this->input->getOption('debug'));

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");

        if(array_key_exists('--clear-config', static::$args))
            filesystem()->deleteDirectory(CONFIG_DIR, true);

        if(array_key_exists('--clear-storage', static::$args))
            filesystem()->deleteDirectory(STORAGE_DIR, true);

        Logger::defineSession();

        info('Loading bot..');

        try
        {
            Config::load();
        }
        catch(\Exception $exception)
        {

            error($exception->getMessage());
            die;
        }

        Setup::migrate();
        HookManager::loadHooks();

        success("Bot loaded.");

        if(empty(config('irc.enabled')))
            warn("There are no IRC servers enabled. Please add and/or enable one or use /connect <server>");

        $this->fromUpdate();

        $this->addSocket('console', new Console());

        foreach(config('irc.servers') as $name => $config)
        {
            if(!in_array($name, config('irc.enabled')))
                continue;

            $irc = new Connection($name, $config);
            $irc->connect();
            $this->addSocket($name, $irc);
        }

        $this->running = true;

        $this->startSockets();
    }

    /**
     * Connects to a network.
     *
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function connect($name)
    {
        if(!array_key_exists($name, config('irc.servers')))
            throw new \Exception("Sever {$name} does not exist.");

        $irc = new Connection($name, config("irc.servers.{$name}"));
        $irc->connect();
        $this->addSocket($name, $irc);

        return true;
    }

    /**
     * Adds a socket to the que.
     *
     * @param $name
     * @param \Dan\Contracts\SocketContract $connection
     */
    public function addSocket($name, SocketContract $connection)
    {
        if(!$this->databaseManager->exists($name))
        {
            $this->databaseManager->create($name);
            Setup::populateDatabase($name);
        }

        $this->connections[$name] = $connection;
    }

    /**
     * Starts reading from all sockets.
     */
    public function startSockets()
    {
        while($this->running)
        {
            usleep(200000);

            $inputs     = $this->getStreams();
            $write      = null;
            $except     = null;

            if(stream_select($inputs, $write, $except, 0) > 0)
            {
                foreach($inputs as $input)
                {
                    foreach($this->connections as $connection)
                    {
                        if($input == $connection->getStream())
                        {
                            static::$currentConnection = $connection->getName();
                            $connection->handle($input);

                        }
                    }
                }
            }
        }
    }

    /**
     * Disconnects from a network.
     *
     * @param $name
     * @throws \Exception
     */
    public static function disconnect($name)
    {
        if(!array_key_exists($name, config('irc.servers')))
            throw new \Exception("Not connection to server {$name}.");

        $connection = static::$dan->connections[$name];

        if($connection instanceof Connection)
        {
            $connection->send("QUIT", "Disconnecting");
            return;
        }

        throw new \Exception("This connection cannot be closed.");
    }

    /**
     * Safely closes the bot.
     *
     * @param string $reason
     * @return bool
     */
    public static function quit($reason = "Bot shutting down")
    {
        if(event('dan.quitting') === false)
            return false;

        controlLog('Shutting down...');

        Config::saveAll();

        database()->save();

        controlLog('Bye!');

        foreach(static::$dan->connections as $connection)
            if($connection instanceof Connection)
                $connection->send("QUIT", $reason);

        die;
    }

    /**
     * Gets all connection streams.
     *
     * @return array
     */
    protected function getStreams() : array
    {
        $streams = [];

        foreach($this->connections as $connection)
            $streams[] = $connection->getStream();

        return $streams;
    }


    /**
     * Does from-update checks.
     */
    public function fromUpdate()
    {
        if($this->input->getOption('from-update'))
        {
            subscribe('irc.packets.join', function(EventArgs $eventArgs) {

                $channel = $this->input->getOption('channel');

                if($eventArgs->get('channel')->getLocation() != $channel)
                    return;

                $v = static::getCurrentGitVersion();
                message($channel, "{reset}[ {green}Up to date {reset}| Currently on {yellow}{$v['id']}{reset} | {cyan}{$v['message']} {reset}]");
                $eventArgs->get('event')->destroy();
            });
        }
    }

    /**
     * Gets the filesystem driver.
     *
     * @return Filesystem
     */
    public static function filesystem() : Filesystem
    {
        return static::$dan->filesystem;
    }

    /**
     * Gets the database driver.
     *
     * @param string $name
     * @return \Dan\Database\Database
     * @throws \Exception
     */
    public static function database($name = null) : Database
    {
        if($name == null)
            $name = static::$currentConnection;

        if($name == null)
            $name = 'console';

        return static::$dan->databaseManager->get($name);
    }

    /**
     * Gets the database manager.
     *
     * @return \Dan\Database\DatabaseManager
     * @throws \Exception
     */
    public static function databaseManager() : DatabaseManager
    {
        return static::$dan->databaseManager;
    }

    /**
     * Checks to see if a connection exists.
     *
     * @param $name
     * @return bool
     */
    public static function hasConnection($name)
    {
        return array_key_exists($name, static::$dan->connections);
    }

    /**
     * Gets the IRC connection.
     *
     * @param null $name
     * @return \Dan\Irc\Connection|SocketContract|MessagingContract
     */
    public static function connection($name = null)
    {
        if($name == null)
            $name = static::$currentConnection;

        if($name == null)
            $name = 'console';

        return static::$dan->connections[$name];
    }

    /**
     * Checks to see if a user is an owner.
     *
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    public static function isOwner(User $user)
    {
        foreach(config('dan.owners') as $usr)
            if (fnmatch($usr, $user->string()))
                return true;

        return false;
    }

    /**
     * Checks to see if a user is an admin.
     *
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    public static function isAdmin(User $user)
    {
        foreach(config('dan.admins') as $usr)
            if (fnmatch($usr, $user->string()))
                return true;

        return false;
    }

    /**
     * Checks to see if a user is an admin or owner.
     *
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    public static function isAdminOrOwner(User $user)
    {
        return (static::isOwner($user) || static::isAdmin($user));
    }

    /**
     * Get program arguments.
     *
     * @param null $arg
     * @param null $default
     * @return array|null
     */
    public static function args($arg = null, $default = null)
    {
        if($arg == null)
            return static::$args;

        if(isset(static::$args[$arg]))
            return static::$args[$arg];

        return $default;
    }

    /**
     * Gets the current git version.
     *
     * @return array
     */
    public static function getCurrentGitVersion() : array
    {
        $commitId       = trim(shell_exec('git rev-parse --short HEAD'));
        $data           = shell_exec('git log -1');
        $messages       = array_filter(explode(PHP_EOL, $data));
        $commitMessage  = trim(last($messages));

        return ['id' => $commitId, 'message' => $commitMessage];
    }


    /**
     * Gets the Dan instance.
     *
     * @return \Dan\Core\Dan
     */
    public static function self()
    {
        return static::$dan;
    }
}