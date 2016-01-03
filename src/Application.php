<?php

namespace Dan;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyApplication
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'dan';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new class() extends Command
 {
     protected function configure()
     {
         $this->setName('dan')
                     ->setDefinition(new InputDefinition([
                         new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Turn debug on'),
                         new InputOption('clear-config', 'cc', InputOption::VALUE_NONE, 'Clear all config on boot'),
                         new InputOption('clear-storage', 'cs', InputOption::VALUE_NONE, 'Clear all storage (including databases) on boot'),
                         new InputOption('from-update', '', InputOption::VALUE_NONE, 'Trigger from-update mode'),
                         new InputOption('channel', '', InputOption::VALUE_REQUIRED, 'Only used in conjunction with --from-update'),
                     ]));
     }

     protected function execute(InputInterface $input, OutputInterface $output)
     {
         (new \Dan\Core\Dan($input, $output))->boot();
     }
 };

        return $defaultCommands;
    }

    /**
     * Gets the InputDefinition related to this Application.
     *
     * @return InputDefinition The InputDefinition instance
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
