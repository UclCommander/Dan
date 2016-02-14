<?php

namespace Dan;

use Dan\Console\Commands\CreateCommandCommand;
use Dan\Console\Commands\DanCommand;
use Dan\Console\Commands\SetupCommand;
use Dan\Setup\Setup;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->setDefaultCommand(Setup::isSetup() ? 'dan' : 'setup');
    }

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new CreateCommandCommand();
        $defaultCommands[] = new DanCommand();
        $defaultCommands[] = new SetupCommand();

        return $defaultCommands;
    }
}
