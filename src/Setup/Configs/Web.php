<?php

namespace Dan\Setup\Configs;

use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Dan\Contracts\ConfigSetupContract;

class Web implements ConfigSetupContract
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
    }

    /**
     * @return Config
     */
    public function setup() : Config
    {
        $config = $this->defaultConfig();

        if (!$this->output->confirm('Should the web listener be enabled? This will allow things like a basic web server and GitHub Webhook support.')) {
            $config->set('web.enabled', false);

            return $config;
        }

        $config->set('web.enabled', true);

        $host = $this->output->ask('What host should I bind to? If you plan on using a domain, use that, otherwise please put the public IP of the server.', '127.0.0.1');
        $config->set('web.host', $host);

        $port = $this->output->ask('What port should the web listener use?', 6969);
        $config->set('web.post', $port);

        return $config;
    }

    /**
     * @return Config
     */
    public function defaultConfig() : Config
    {
        return new Config([
            'web' => [
                'enabled'   => true,
                'host'      => '127.0.0.1',
                'port'      => 6969,
            ],
        ]);
    }

    /**
     * @return mixed
     */
    public function introText()
    {
        return "Let's setup the web listener..";
    }
}
