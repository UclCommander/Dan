<?php namespace Dan\Commands\Command;

use Dan\Contracts\CommandContract;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Plugin implements CommandContract {

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function run(Channel $channel, User $user, $message)
    {
        $data = explode(' ', $message);

        try
        {
            switch ($data[0])
            {
                case 'loaded':
                    $user->sendNotice("Loaded Plugins: " . implode(', ', Dan::service('pluginManager')->loaded()));
                    break;
                case 'load':
                    Dan::service('pluginManager')->loadPlugin($data[1]);
                    $user->sendNotice("Plugin {$data[1]} loaded.");
                    break;

                case 'reload':
                    Dan::service('pluginManager')->unloadPlugin($data[1]);
                    Dan::service('pluginManager')->loadPlugin($data[1]);
                    $user->sendNotice("Plugin {$data[1]} reloaded.");
                    break;

                case 'unload':
                    Dan::service('pluginManager')->unloadPlugin($data[1]);
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
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     * @return mixed
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("plugin load <plugin> - Loads <plugin>");
        $user->sendNotice("plugin reload <plugin> - Reloads <plugin>");
        $user->sendNotice("plugin unload <plugin> - Unloads <plugin>");
    }
}