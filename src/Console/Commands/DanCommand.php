<?php

namespace Dan\Console\Commands;

use Dan\Core\Dan;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DanCommand extends Command
{
    protected function configure()
    {
        $this->setName('dan')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Turn debug on');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dan = new Dan($input, $output);

        console()->info('-- Dan '.Dan::VERSION.' --');
        console()->info('Loading bot...');

        $dan->boot();

        console()->success('Bot loaded.');

        $dan->run();
    }
}
