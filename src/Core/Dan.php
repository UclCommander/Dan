<?php namespace Dan\Core; 

use Dan\Console\Console;
use Dan\Helpers\Setup;
use Illuminate\Filesystem\Filesystem;

class Dan {

    /**
     * @var Filesystem $filesystem
     */
    protected $filesystem;

    protected static $dan;

    public function __construct()
    {
        static::$dan = $this;

        $this->filesystem = new Filesystem();
    }

    public function boot()
    {
        global $argv;

        $args = Console::parseArgs($argv);

        info('Loading bot..');

        if(!Setup::isSetup())
        {
            info("It appears this is a first time run, setting defaults up.");

            Setup::runSetup();
        }

        // If dan.debug is true, --debug is true, or we're running outside the PHAR file, turn on debug.
        define('DEBUG', (config('dan.debug') || (array_key_exists('--debug', $args) && $args['--debug'] == 'true')) || !PHAR);

        if(DEBUG)
            debug("!!!DEBUG MODE ACTIVATED!!!");
    }

    /**
     * @return Filesystem
     */
    public static function filesystem()
    {
        return static::$dan->filesystem;
    }

}