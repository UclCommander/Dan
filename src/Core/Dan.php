<?php namespace Dan\Core; 

use Dan\Console\Console;
use Dan\Helpers\Setup;
use Dan\Irc\Connection;
use Illuminate\Filesystem\Filesystem;

class Dan {

    const VERSION = '1.0.1dev';

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    /** @var Connection $connection */
    protected $connection;

    /** @var static $dan  */
    protected static $dan;

    /**
     * \
     */
    public function __construct()
    {
        static::$dan = $this;

        $this->filesystem = new Filesystem();
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

        if(!Setup::isSetup())
        {
            alert("It appears this is a first time run, or there was an update. Setting defaults up.");
            Setup::runSetup();
            alert("Setup complete.");
        }

        // If dan.debug is true, --debug is true, or we're running outside the PHAR file, turn on debug.
        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', $args) && $args['--debug'] == 'true')) || !PHAR);

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");

        $this->connection = new Connection();
        $this->connection->start();
    }

    /**
     * @return Filesystem
     */
    public static function filesystem()
    {
        return static::$dan->filesystem;
    }

}