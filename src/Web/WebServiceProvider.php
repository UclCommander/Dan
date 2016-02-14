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
        connection()->addConnection(new Listener(config('web')));
    }
}