<?php namespace Dan\Core; 


use Dan\Console\Console;
use Illuminate\Filesystem\Filesystem;

class Dan {

    protected $filesystem;

    public function boot()
    {
        Console::info('Loading bot..');

        $this->createDirectories();


    }


    /**
     * Creates directories if they don't exist
     */
    public function createDirectories()
    {
        Console::debug('Creating directories...');

        $this->filesystem = new Filesystem();

        if(!$this->filesystem->exists(PLUGIN_DIR))
        {
            Console::debug("Directory '" . PLUGIN_DIR ."' not found, creating.");
            $this->filesystem->makeDirectory(PLUGIN_DIR);
        }

        if(!$this->filesystem->exists(CONFIG_DIR))
        {
            Console::debug("Directory '" . CONFIG_DIR ."' not found, creating.");
            $this->filesystem->makeDirectory(CONFIG_DIR);
        }

        if(!$this->filesystem->exists(STORAGE_DIR))
        {
            Console::debug("Directory '" . STORAGE_DIR ."' not found, creating.");
            $this->filesystem->makeDirectory(STORAGE_DIR);
        }

        if(!$this->filesystem->exists(STORAGE_DIR . '/database/'))
        {
            Console::debug("Directory '" . STORAGE_DIR ."/database/' not found, creating.");
            $this->filesystem->makeDirectory(STORAGE_DIR . '/database/');
        }

        if(!$this->filesystem->exists(STORAGE_DIR . '/plugins/'))
        {
            Console::debug("Directory '" . STORAGE_DIR ."/plugins/' not found, creating.");
            $this->filesystem->makeDirectory(STORAGE_DIR . '/plugins/');
        }

        if(!$this->filesystem->exists(ROOT_DIR . '/logs/'))
        {
            Console::debug("Directory '" . STORAGE_DIR ."/logs/' not found, creating.");
            $this->filesystem->makeDirectory(ROOT_DIR . '/logs/');
        }
    }
}