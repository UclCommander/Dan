<?php namespace Plugins\Commands\Command;

use Dan\Core\Dan;
use Dan\Irc\Channel;
use Dan\Irc\User;
use Plugins\Commands\CommandInterface;

class Plugin implements CommandInterface {

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Channel $channel
     * @param \Dan\Irc\User    $user
     * @param                  $message
     * @return void
     */
    public function run(Channel $channel, User $user, $message)
    {
        $data = explode(' ', $message);

        try
        {
            switch ($data[0])
            {
                case 'load':
                    Dan::app('pluginManager')->loadPlugin($data[1]);
                    $user->sendNotice("Plugin {$data[1]} loaded.");
                    break;

                case 'reload':
                    Dan::app('pluginManager')->unloadPlugin($data[1]);
                    Dan::app('pluginManager')->loadPlugin($data[1]);
                    $user->sendNotice("Plugin {$data[1]} reloaded.");
                    break;

                case 'unload':
                    Dan::app('pluginManager')->unloadPlugin($data[1]);
                    $user->sendNotice("Plugin {$data[1]} unloaded.");
                    break;
            }

        }
        catch(\Exception $e)
        {
            $user->sendNotice($e->getMessage());
        }
    }

    /**
     * Command help.
     *
     * @param \Dan\Irc\User $user
     * @param               $message
     * @return mixed
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("plugin load <plugin> - Loads <plugin>");
        $user->sendNotice("plugin reload <plugin> - Reloads <plugin>");
        $user->sendNotice("plugin unload <plugin> - Unloads <plugin>");
    }
}