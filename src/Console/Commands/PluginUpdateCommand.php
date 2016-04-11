<?php

namespace Dan\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUpdateCommand extends Command
{
    protected function configure()
    {
        $this->setName('plugin:update')
            ->setDescription('Updates all installed plugins');
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
        shell_exec('cd '.ROOT_DIR.'/plugins && composer update');
    }
}
