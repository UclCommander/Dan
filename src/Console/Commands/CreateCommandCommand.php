<?php

namespace Dan\Console\Commands;

use Dan\Commands\Commands\Create;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommandCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('make:command');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Create($input, $output))->create();
    }
}
