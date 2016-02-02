<?php

namespace Dan\Irc;

use Illuminate\Support\ServiceProvider;

class IrcServiceProvider extends ServiceProvider
{
    protected $config;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->config = config('irc');

        $this->loadAutoConnectServers();
    }

    /**
     *
     */
    protected function loadAutoConnectServers()
    {
        foreach ($this->config['auto_connect'] as $server) {
            $this->connect($server);
        }
    }

    /**
     * @param $server
     */
    protected function connect($server)
    {
        $config = $this->config['servers'][$server];

        $connection = new Connection($server, $config);

        $connection->createDatabase();

        connection()->addConnection($connection);
    }
}
