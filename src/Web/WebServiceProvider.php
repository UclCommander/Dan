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
        $config = config('web');

        if (!$config['enabled']) {
            return;
        }

        $listener = new Listener($config);
        connection()->addConnection($listener);
        $this->app->instance('web', $listener);
    }
}
