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

        $listener = new Listener($config);
        $this->app->instance('web', $listener);

        if ($config['enabled']) {
            connection()->addConnection($listener);
        }
    }
}
