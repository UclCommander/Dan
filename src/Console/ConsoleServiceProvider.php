<?php

namespace Dan\Console;

use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $console = new Connection();
        $console->connect();

        $this->app->make('connections')->addConnection($console);

        $this->app->instance('console', new Console($console));
    }
}
