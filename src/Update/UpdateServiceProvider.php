<?php namespace Dan\Update;


use Illuminate\Support\ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance('updater', new Updater());
    }
}