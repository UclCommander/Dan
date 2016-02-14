<?php namespace Dan\Setup\Configs;


use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Dan\Contracts\ConfigSetupContract;

class Irc implements ConfigSetupContract
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    protected $name = 'byteirc';

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
    }

    /**
     * @return Config
     */
    public function setup() : Config
    {
        $name = $this->output->ask('Lets start by choosing a name for the server', 'byteirc');
        $this->name = $name;

        $config = $this->defaultConfig();

        $config->set('irc.auto_connect', []);
        $config->set("irc.servers.{$name}", []);

        $server = $this->output->ask("The IRC server I'm connecting to", 'irc.byteirc.org');
        $config->set("irc.servers.{$name}.server", $server);

        $port = $this->output->ask('Port', 6667);
        $config->set("irc.servers.{$name}.port", $port);

        $nick = $this->output->ask('Lets choose a nickname');
        $config->set("irc.servers.{$name}.user.nick", $nick);

        $user = $this->output->ask('Username too');
        $config->set("irc.servers.{$name}.user.name", $user);

        $real = $this->output->ask('My real name', 'Dan the IRC Bot by UclCommander');
        $config->set("irc.servers.{$name}.user.real", $real);

        $config->set("irc.servers.{$name}.user.pass", '');
        $this->output->note("You'll have to set the password yourself after we're done here.");

        $channels = $this->output->ask(
            'What channels do you want me to join? You can separate them by a comma.',
            '#UclCommander,#DanControl'
        );
        $config->set("irc.servers.{$name}.channels", explode(',', $channels));

        if ($this->output->confirm('Should I automatically connect to this network?')) {
            $config->push('irc.auto_connect', $name);
        }

        $config->set("irc.servers.{$name}.command_prefix", '$');

        return $config;
    }

    /**
     * @return Config
     */
    public function defaultConfig() : Config
    {
        return new Config([
            'irc' => [
                'auto_connect' => [
                    $this->name,
                ],
                'servers' => [
                    $this->name => [
                        'server'  => 'irc.byteirc.org',
                        'port'    => 6667,
                        'user'    => [
                            'nick'  => 'Dan',
                            'name'  => 'Dan',
                            'real'  => 'Dan the IRC Bot by UclCommander',
                            'pass'  => '',
                        ],
                        'channels' => [
                            '#UclCommander',
                            '#DanControl',
                        ],
                        'command_prefix' => '$',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return mixed
     */
    public function introText()
    {
        return "I'm going to need an IRC server to connect too right? Lets do that.";
    }
}