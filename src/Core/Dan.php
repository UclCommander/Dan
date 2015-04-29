<?php namespace Dan\Core; 

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
        info('Loading bot..');

        if(!Setup::isSetup())
        {
            info("It appears this is a first time run, setting defaults up.");

            Setup::runSetup();
        }
    }

    /**
     * @return Filesystem
     */
    public static function filesystem()
    {
        return static::$dan->filesystem;
    }

}