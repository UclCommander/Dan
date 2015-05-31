<?php namespace Dan\Core;

use Composer\Autoload\ClassLoader;
use Dan\Commands\CommandManager;
use Dan\Console\Console;
use Dan\Helpers\Setup;
use Dan\Database\Database;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Plugins\PluginManager;
use Illuminate\Filesystem\Filesystem;

class Dan {

    const VERSION = '4.0.15-dev';

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /** @var Connection $connection */
    protected $connection;

    protected $commandManager;

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

        info('Loading bot..');

        try
        {
            Config::load();
        }
        catch(\Exception $exception)
        {
            Console::critical($exception->getMessage(), true);

        }
        if(!Setup::isSetup())
        {
            alert("It appears this is a first time run, or there was an update. Setting defaults up.");

            $first = Setup::runSetup();

            alert("Setup complete.");

            if($first)
            {
                alert("This was a first time setup. You can now configure the bot in the config directory.");
                die;
            }
        }

        // If dan.debug is true, --debug is true, or we're running outside the PHAR file, turn on debug.
        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', $args) && $args['--debug'] == 'true')) || !PHAR);

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");

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

        event('dan.loaded');

        info("Bot loaded.");

        $this->connection = new Connection();
        $this->connection->start();
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
}