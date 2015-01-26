<?php namespace Plugins\Commands;

use Dan\Contracts\PluginContract;
use Dan\Core\Dan;
use Dan\Plugins\Plugin;
use Plugins\Commands\Command\Hash;
use Plugins\Commands\Command\Join;
use Plugins\Commands\Command\Memory;
use Plugins\Commands\Command\Part;
use Plugins\Commands\Command\Ping;
use Plugins\Commands\Command\Say;

class Commands extends Plugin implements PluginContract {

    protected $version     = '2.0';
    protected $author      = "UclCommander";
    protected $description = "Dan's command plugin";

    /**
     *
     */
    public function register()
    {
        Dan::service('commands')->addCommand('hash',    new Hash());
        Dan::service('commands')->addCommand('join',    new Join());
        Dan::service('commands')->addCommand('memory',  new Memory());
        Dan::service('commands')->addCommand('part',    new Part());
        Dan::service('commands')->addCommand('ping',    new Ping());
        Dan::service('commands')->addCommand('say',     new Say());
    }

    public function unregister()
    {
        parent::unregister();

        Dan::service('commands')->removeCommand('hash');
        Dan::service('commands')->removeCommand('join');
        Dan::service('commands')->removeCommand('memory');
        Dan::service('commands')->removeCommand('part');
        Dan::service('commands')->removeCommand('ping');
        Dan::service('commands')->removeCommand('say');
    }
}