<?php namespace Dan\Core;

use Composer\Autoload\ClassLoader;
use Dan\Commands\CommandManager;
use Dan\Console\Console;
use Dan\Events\EventArgs;
use Dan\Helpers\Hooks;
use Dan\Helpers\Logger;
use Dan\Database\Database;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Plugins\PluginManager;
use Dan\Setup\Migrate;
use Illuminate\Filesystem\Filesystem;

class Dan {

    const VERSION = '4.0.16-dev';

    protected static $args = [];

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /** @var Connection $connection */
    protected $connection;

    /** @var CommandManager $commandManager */
    protected $commandManager;

    /** @var PluginManager $pluginManager */
    protected $pluginManager;

    /** @var static $dan  */
    protected static $dan;

    /** @var Database */
    protected $database;

    protected $composer;


    /**
     * @param \Composer\Autoload\ClassLoader $composer
     */
    public function __construct(ClassLoader $composer)
    {
        static::$dan = $this;

        $this->filesystem   = new Filesystem();
        $this->database     = new Database('database');

        $this->composer = $composer;
    }

    /**
     * Boots up Dan
     */
    public function boot()
    {
        global $argv;

        $args = Console::parseArgs($argv);

        static::$args = $args;

        Logger::defineSession();


        info('Loading bot..');

        // If dan.debug is true, --debug is true, or we're running outside the PHAR file, turn on debug.
        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', $args) && $args['--debug'] == 'true')) || !PHAR);

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");

        try
        {
            Config::load();
        }
        catch(\Exception $exception)
        {
            Console::critical($exception->getMessage(), true);
        }

        Migrate::checkAndDo();

        $this->database->load();

        alert("Indexing commands...");
        $this->commandManager   = new CommandManager();

        alert("Loading Plugins...");
        $this->pluginManager    = new PluginManager();

        foreach(config('dan.plugins') as $plugin)
        {
            try
            {
                info("Loading plugin {$plugin}");
                $this->pluginManager->loadPlugin($plugin);
            }
            catch(\Exception $e)
            {
                Console::exception($e);
            }
        }

        Hooks::registerHooks();

        event('dan.loaded');
        info("Bot loaded.");

        if(static::args('--from') == 'update')
        {
            subscribe('irc.packets.join', function(EventArgs $eventArgs) {

                if($eventArgs->get('channel')->getLocation() != static::args('--channel'))
                    return;

                $v = static::getCurrentGitVersion();
                message(static::args('--channel'), "{reset}[ {green}Up to date {reset}| Currently on {yellow}{$v['id']}{reset} | {cyan}{$v['message']} {reset}]");
                $eventArgs->get('event')->destroy();
            });
        }

        $this->connection = new Connection();
        $this->connection->start();
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

        static::plugins()->unloadAll();

        Config::saveAll();

        database()->save();

        controlLog('Bye!');

        static::connection()->send("QUIT", $reason);

        return true;
    }

    /**
     * Gets the filesystem driver.
     *
     * @return Filesystem
     */
    public static function filesystem()
    {
        return static::$dan->filesystem;
    }

    /**
     * Gets the database driver.
     *
     * @return Database
     */
    public static function database()
    {
        return static::$dan->database;
    }
    /**
     * Gets the database driver.
     *
     * @return CommandManager
     */
    public static function commands()
    {
        return static::$dan->commandManager;
    }

    /**
     * Gets the IRC connection.
     *
     * @return Connection
     */
    public static function connection()
    {
        return static::$dan->connection;
    }

    /**
     * Gets the Plugin Manager.
     *
     * @return PluginManager
     */
    public static function plugins()
    {
        return static::$dan->pluginManager;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function composer()
    {
        return static::$dan->composer;
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
     * Get program arguments
     *
     * @param null $arg
     * @return array|null
     */
    public static function args($arg = null)
    {
        if($arg == null)
            return static::$args;

        if(isset(static::$args[$arg]))
            return static::$args[$arg];

        return null;
    }

    /**
     * Gets the current git version.
     *
     * @return array
     */
    public static function getCurrentGitVersion()
    {
        $commitId       = trim(shell_exec('git rev-parse --short HEAD'));
        $data           = shell_exec('git log -1');
        $messages       = array_filter(explode(PHP_EOL, $data));
        $commitMessage  = trim(last($messages));

        return ['id' => $commitId, 'message' => $commitMessage];
    }
}