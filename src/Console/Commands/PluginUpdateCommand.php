<?php

namespace Dan\Console\Commands;

use Dan\Console\OutputStyle;
use Dan\Setup\Setup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateCommand extends Command
{
    /**
     * 
     */
    protected function configure()
    {
        $this->setName('plugin:update')
            ->setDescription('Updates all installed plugins');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        shell_exec('cd ' . ROOT_DIR . '/plugins && composer update');
    }
}
