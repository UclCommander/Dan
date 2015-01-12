<?php namespace Plugins\Fun; 


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
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');

        $command->register('fml',   new FML());
        $command->register('lenny', new Lenny());
        $command->register('nbc',   new Nbc());
        $command->register('trp',   new Trp());
        $command->register('urban', new Urban());
    }

    public function unregister()
    {
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');
        $command->unregister('fml');
        $command->unregister('lenny');
        $command->unregister('nbc');
        $command->unregister('trp');
        $command->unregister('urban');
    }
}