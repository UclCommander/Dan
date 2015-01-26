<?php namespace Plugins\Fun; 


use Dan\Commands\CommandManager;
use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;
use Plugins\Fun\Commands\FML;
use Plugins\Fun\Commands\Lenny;
use Plugins\Fun\Commands\Nbc;
use Plugins\Fun\Commands\Trp;
use Plugins\Fun\Commands\Urban;

class Fun extends Plugin implements PluginContract {

    protected $requires = [
        'Commands'
    ];

    public function register()
    {
        /** @var CommandManager $command */
        $command = Dan::service('commands');

        $command->addCommand('fml',   new FML());
        $command->addCommand('lenny', new Lenny());
        $command->addCommand('nbc',   new Nbc());
        $command->addCommand('trp',   new Trp());
        $command->addCommand('urban', new Urban());
    }

    public function unregister()
    {
        /** @var CommandManager $command */

        $command = Dan::service('commands');

        $command->removeCommand('fml');
        $command->removeCommand('lenny');
        $command->removeCommand('nbc');
        $command->removeCommand('trp');
        $command->removeCommand('urban');
    }
}