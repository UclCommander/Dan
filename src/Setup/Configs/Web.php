<?php

namespace Dan\Setup\Configs;

use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Dan\Contracts\ConfigSetupContract;

class Web implements ConfigSetupContract
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
    }

    /**
     * @return Config
     */
    public function setup() : Config
    {
        return $this->defaultConfig();
    }

    /**
     * @return Config
     */
    public function defaultConfig() : Config
    {
        return new Config([
            'web' => [
                'enabled'   => true,
                'host'      => '127.0.0.1',
                'port'      => 6969,
            ],
        ]);
    }

    /**
     * @return mixed
     */
    public function introText()
    {
        return 'Let me setup the defaults for the web listener..';
    }
}
