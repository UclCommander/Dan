<?php namespace Dan\Core;

use Dan\Console\Console;
use Dan\Database\Database;
use Dan\Database\DatabaseManager;
use Dan\Events\EventArgs;
use Dan\Helpers\Logger;
use Dan\Irc\Connection;
use Dan\Irc\Location\User;
use Dan\Setup\Migrate;
use Illuminate\Filesystem\Filesystem;

class Dan {

    const VERSION = '5.0.0-dev';

    protected static $args = [];

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /** @var Connection $connection */
    protected $connection;

    /** @var static $dan  */
    protected static $dan;

    /** @var DatabaseManager */
    protected $databaseManager;

    /**
     *
     */
    public function __construct()
    {
        static::$dan = $this;

        $this->filesystem       = new Filesystem();
        $this->databaseManager  = new DatabaseManager();
    }

    /**
     * Boots up Dan
     */
    public function boot()
    {
        global $argv;

        static::$args = Console::parseArgs($argv);

        if(array_key_exists('--clear-config', static::$args))
            filesystem()->deleteDirectory(CONFIG_DIR, true);

        if(array_key_exists('--clear-storage', static::$args))
            filesystem()->deleteDirectory(STORAGE_DIR, true);

        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', static::$args) && static::$args['--debug'] == 'true')));

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");

        Logger::defineSession();

        info('Loading bot..');

        try
        {
            Config::load();
        }
        catch(\Exception $exception)
        {
            Console::critical($exception->getMessage(), true);
        }

        Migrate::checkAndDo();

        if(!$this->databaseManager->loaded('database'))
            $this->databaseManager->loadDatabase('database');

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
    public static function quit($reason = "Bot shutting down") : bool
    {
        if(event('dan.quitting') === false)
            return false;

        controlLog('Shutting down...');

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
    public static function database($name = 'database') : Database
    {
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
     * Gets the IRC connection.
     *
     * @return Connection
     */
    public static function connection() : Connection
    {
        return static::$dan->connection;
    }

    /**
     * Checks to see if a user is an owner.
     *
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    public static function isOwner(User $user) : bool
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
    public static function isAdmin(User $user) : bool
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
    public static function isAdminOrOwner(User $user) : bool
    {
        return (static::isOwner($user) || static::isAdmin($user));
    }

    /**
     * Get program arguments
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
}