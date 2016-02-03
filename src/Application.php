<?php

namespace Dan;

use Dan\Console\Commands\DanCommand;
use Dan\Console\Commands\SetupCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends SymfonyApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return file_exists(ROOT_DIR.'/config/dan.json') ? 'dan' : 'setup';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new DanCommand();
        $defaultCommands[] = new SetupCommand();

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
