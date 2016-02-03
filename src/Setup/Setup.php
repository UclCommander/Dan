<?php

namespace Dan\Setup;

use Dan\Config\Config;
use Dan\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Setup
{
    /**
     * @var \Dan\Console\OutputStyle
     */
    protected $output;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Setup constructor.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $inputInterface
     * @param \Symfony\Component\Console\Output\OutputInterface $outputInterface
     */
    public function __construct(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        $this->output = new OutputStyle($inputInterface, $outputInterface);
    }

    /**
     * Does the setup, LIKE A BOSS!
     */
    public function doSetup()
    {
        $this->config = new Config($this->defaultConfig());

        $this->output->section('Lets set this baby up!');

        if (!$this->output->confirm('Do you want to skip setup and add initial values?', false)) {
            $host = $this->output->ask(
                "Since you're my owner, what's you're hostmask? Please use nick!user@host format. You can use wildcards (*) too!"
            );

            $this->config->push('dan.owners', $host);

            if ($this->output->confirm('Should I automatically check for updates?')) {
                $this->config->set('dan.updates.auto_check', true);

                if ($this->output->confirm('Should I automatically install updates?')) {
                    $this->config->set('dan.updates.auto_install', true);
                }
            }

            $this->output->section('Now, lets set up an initial IRC server.');
            $this->config->set('irc.auto_connect', []);

            $name = $this->output->ask('Lets start by choosing a name for the server', 'byteirc');
            $this->config->set("irc.servers.{$name}", []);

            $server = $this->output->ask("The IRC server I'm connecting to", 'irc.byreirc.org');
            $this->config->set("irc.servers.{$name}.server", $server);

            $port = $this->output->ask('Port', 6667);
            $this->config->set("irc.servers.{$name}.port", $port);

            $nick = $this->output->ask('Lets choose a nickname');
            $this->config->set("irc.servers.{$name}.user.nick", $nick);

            $user = $this->output->ask('Username too');
            $this->config->set("irc.servers.{$name}.user.name", $user);

            $real = $this->output->ask('My real name', 'Dan the IRC Bot by UclCommander');
            $this->config->set("irc.servers.{$name}.user.real", $real);

            $this->config->set("irc.servers.{$name}.user.pass", '');
            $this->output->note("You'll have to set the password yourself after we're done here.");

            $channels = $this->output->ask(
                'What channels do you want me to join? You can separate them by a comma.',
                '#UclCommander,#DanControl'
            );
            $this->config->set("irc.servers.{$name}.channels", explode(',', $channels));

            if ($this->output->confirm('Should I automatically connect to this network?')) {
                $this->config->push('irc.auto_connect', $name);
            }

            $this->config->set("irc.servers.{$name}.command_prefix", '$');
        }

        $this->output->success('Configuration is complete! All you have to do now is start me up again.');
        $this->makeConfigFiles();
    }

    /**
     * Make the config files.
     */
    protected function makeConfigFiles()
    {
        foreach ($this->config->toArray() as $key => $value) {
            file_put_contents(ROOT_DIR."/config/{$key}.json", json_encode($value, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Create the default config settings.
     *
     * @return array
     */
    protected function defaultConfig()
    {
        return [
            'dan' => [
                'debug'   => false,
                'updates' => [
                    'auto_check'   => false,
                    'auto_install' => false,
                ],
                'owners'    => [],
                'admins'    => [],
                'providers' => [
                    \Dan\Commands\CommandServiceProvider::class,
                    \Dan\Irc\IrcServiceProvider::class,
                ],
            ],
            'irc' => [
                'auto_connect' => [
                    'byteirc',
                ],
                'servers' => [
                    'byteirc' => [
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
        ];
    }
}
