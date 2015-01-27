<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;
use Plugins\Commands\Commands\Hash;
use Plugins\Commands\Commands\Ping;
use Plugins\Commands\Commands\FML;
use Plugins\Commands\Commands\Lenny;
use Plugins\Commands\Commands\Nbc;
use Plugins\Commands\Commands\Trp;
use Plugins\Commands\Commands\Urban;

class Commands extends Plugin implements PluginContract {

    protected $version     = '2.0';
    protected $author      = "UclCommander";
    protected $description = "Dan's command plugin";

    /**
     *
     */
    public function register()
    {
        $command = Dan::service('commands');
        $command->addCommand('fml',     new FML());
        $command->addCommand('hash',    new Hash());
        $command->addCommand('lenny',   new Lenny());
        $command->addCommand('nbc',     new Nbc());
        $command->addCommand('ping',    new Ping());
        $command->addCommand('trp',     new Trp());
        $command->addCommand('urban',   new Urban());
    }

    public function unregister()
    {
        parent::unregister();

        $command = Dan::service('commands');
        $command->removeCommand('fml');
        $command->removeCommand('hash');
        $command->removeCommand('lenny');
        $command->removeCommand('nbc');
        $command->removeCommand('trp');
        $command->removeCommand('ping');
        $command->removeCommand('urban');
    }
}