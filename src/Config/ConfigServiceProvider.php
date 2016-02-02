<?php namespace Dan\Config;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = new Config();

        $config->load();

        $this->app->instance('config', $config);
    }
}