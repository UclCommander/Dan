<?php

namespace Dan\Contracts;

use Dan\Config\Config;

interface ConfigSetupContract
{
    /**
     * @return Config
     */
    public function setup() : Config;

    /**
     * @return Config
     */
    public function defaultConfig() : Config;

    /**
     * @return mixed
     */
    public function introText();
}
