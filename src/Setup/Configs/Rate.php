<?php

namespace Dan\Setup\Configs;

use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Dan\Contracts\ConfigSetupContract;

class Rate implements ConfigSetupContract
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
            'rate' => [
                'kick_from_spam' => 8,
                'default'        => [2, 5],
                'commands'       => [
                    'command'    => [1, 8],
                ],
            ],
        ]);
    }

    /**
     * @return mixed
     */
    public function introText()
    {
        return false;
    }
}
