<?php

namespace Dan\Setup;

use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Dan\Contracts\ConfigSetupContract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Setup
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    /**
     * @var Config
     */
    protected $config;

    protected static $setupFiles = [
        'dan' =>  \Dan\Setup\Configs\Dan::class,
        'irc' => \Dan\Setup\Configs\Irc::class,
        'web' => \Dan\Setup\Configs\Web::class,
    ];

    /**
     * Setup constructor.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $inputInterface
     * @param \Symfony\Component\Console\Output\OutputInterface $outputInterface
     */
    public function __construct(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        $this->output = new OutputStyle($inputInterface, $outputInterface);
    }

    /**
     * @return bool
     */
    public static function isSetup() : bool
    {
        foreach (static::$setupFiles as $key => $setup) {
            if (!file_exists(ROOT_DIR."/config/{$key}.json")) {
                return false;
            }
        }

        return true;
    }

    /**
     * Does the setup, LIKE A BOSS!
     */
    public function doSetup()
    {
        $this->output->section('Lets set this baby up!');

        $skip = $this->output->confirm('Do you want to skip setup and add initial values?', false);

        $configs = new Config();

        foreach (static::$setupFiles as $key => $setup) {
            if (file_exists(ROOT_DIR."/config/{$key}.json")) {
                continue;
            }

            /** @var ConfigSetupContract $class */
            $class = new $setup($this->output);

            $this->output->section($class->introText());

            $config = ($skip ? $class->defaultConfig() : $class->setup());
            $config = $config->toArray();

            $key = key($config);

            $configs->set($key, reset($config));
        }

        $this->output->success('Configuration is complete! All you have to do now is start me up again.');
        $this->makeConfigFiles($configs);
    }

    /**
     * Make the config files.
     *
     * @param $config
     */
    protected function makeConfigFiles(Config $config)
    {
        if (!file_exists(ROOT_DIR."/config/")) {
            mkdir(ROOT_DIR."/config/");
        }

        foreach ($config->toArray() as $key => $value) {
            file_put_contents(ROOT_DIR."/config/{$key}.json", json_encode($value, JSON_PRETTY_PRINT));
        }
    }
}
