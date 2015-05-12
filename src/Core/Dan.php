<?php namespace Dan\Core; 

use Dan\Commands\CommandManager;
use Dan\Console\Console;
use Dan\Helpers\Setup;
use Dan\Database\Database;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Illuminate\Filesystem\Filesystem;

class Dan {

    const VERSION = '4.0.10dev';

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /** @var Connection $connection */
    protected $connection;

    protected $commandManager;

    /** @var static $dan  */
    protected static $dan;

    /** @var Database */
    protected $database;

    /**
     *
     */
    public function __construct()
    {
        static::$dan = $this;

        $this->filesystem   = new Filesystem();
        $this->database     = new Database('database');
    }

    /**
     * Boots up Dan
     */
    public function boot()
    {
        global $argv;

        $args = Console::parseArgs($argv);

        info('Loading bot..');

        Config::load();

        event('dan.loading');

        if(!Setup::isSetup())
        {
            event('dan.setup.start');
            alert("It appears this is a first time run, or there was an update. Setting defaults up.");

            Setup::runSetup();

            alert("Setup complete.");
            event('dan.setup.end');
        }

        // If dan.debug is true, --debug is true, or we're running outside the PHAR file, turn on debug.
        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', $args) && $args['--debug'] == 'true')) || !PHAR);

        if(DEBUG)
        {
            event('dan.debug.activate');
            debug("!!!DEBUG MODE ACTIVATED!!!");
        }

        $this->database->load();

        alert("Indexing commands...");
        $this->commandManager   = new CommandManager();

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
     * Gets the IRC connection.
     *
     * @return Connection
     */
    public static function connection()
    {
        return static::$dan->connection;
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
            if (fnmatch($usr, "{$user->nick()}!{$user->user()}@{$user->host()}"))
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
            if (fnmatch($usr, "{$user->nick()}!{$user->user()}@{$user->host()}"))
                return true;

        return false;
    }
}