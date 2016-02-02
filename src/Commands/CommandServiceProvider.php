<?php

namespace Dan\Commands;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $manager = new CommandManager();

        $this->app->instance('commands', $manager);
    }
}
