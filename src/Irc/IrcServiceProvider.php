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

    protected function loadAutoConnectServers()
    {
        foreach ($this->config['auto_connect'] as $server) {
            $this->connect($server);
        }
    }

    /**
     * @param $server
     *
     * @return bool
     */
    public function connect($server) : bool
    {
        $config = $this->config['servers'][$server];

        $connection = new Connection($server, $config);
        $connected = connection()->addConnection($connection);

        return is_bool($connected) ? $connected : false;
    }
}
