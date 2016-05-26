<?php

namespace Dan\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginEnableCommand extends Command
{
    protected function configure()
    {
        $this->setName('plugin:enable')
            ->setDescription('Enables a plugin')
            ->addArgument('name', InputArgument::REQUIRED, 'Plugin name');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Enabling plugin...');

        $file = ROOT_DIR.'/config/dan.json';
        $config = json_decode(file_get_contents($file), true);
        $name = $input->getArgument('name');

        if (!file_exists(ROOT_DIR."/plugins/{$name}/plugin.json")) {
            throw new \Exception("plugin.json not found for {$name}");
        }

        $plugin = json_decode(file_get_contents(ROOT_DIR."/plugins/{$name}/plugin.json"), true);

        if (in_array($plugin['provider'], $config['providers'])) {
            throw new \Exception('This plugin is already enabled.');
        }

        $config['providers'][] = $plugin['provider'];

        file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT));
        
        $output->writeln('Enabled');
    }
}
