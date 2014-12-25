<?php namespace Plugins\Fun; 


use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;
use Plugins\Fun\Commands\Lenny;
use Plugins\Fun\Commands\Nbc;
use Plugins\Fun\Commands\Trp;

class Fun extends Plugin implements PluginContract {

    protected $requires = [
        'Commands'
    ];

    public function register()
    {
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');

        $command->register('lenny', new Lenny());
        $command->register('nbc',   new Nbc());
        $command->register('trp',   new Trp());
    }

    public function unregister()
    {
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');
        $command->unregister('lenny');
        $command->unregister('nbc');
        $command->unregister('trp');
    }
}