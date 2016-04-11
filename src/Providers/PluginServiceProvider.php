<?php

namespace Dan\Providers;

use Dan\Contracts\PluginContract;
use Illuminate\Support\ServiceProvider;

abstract class PluginServiceProvider extends ServiceProvider implements PluginContract
{
    /**
     * Creates the config file for the plugin.
     *
     * @return array
     */
    public function config() : array
    {
        return [];
    }
}
