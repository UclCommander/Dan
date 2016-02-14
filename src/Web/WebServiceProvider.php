<?php

namespace Dan\Web;

use Illuminate\Support\ServiceProvider;

class WebServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $listener = new Listener(config('web'));
        connection()->addConnection($listener);
        $this->app->instance('web', $listener);
    }
}