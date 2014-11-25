<?php namespace Plugins\Fun; 


use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;
use Plugins\Fun\Commands\Lenny;

class Fun extends Plugin implements PluginContract {

    public function register()
    {
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');

        $command->register('lenny', new Lenny());
    }

    public function unregister()
    {
        /** @var \Plugins\Commands\CommandManager $command */
        $command = Dan::app('commandManager');
        $command->unregister('lenny');
    }
}