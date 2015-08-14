<?php

use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Location $location */
/** @var string $message */
/** @var string $entry */

if($entry == 'use' || $entry == 'console')
{
    $data = explode(' ', $message);

    try
    {
        switch ($data[0])
        {
            case 'load':
                if(plugins()->loadPlugin($data[1]))
                    message($location, "Plugin '{$data[1]}' loaded.");
                break;

            case 'unload':
                if(plugins()->unloadPlugin($data[1]))
                    message($location, "Plugin '{$data[1]}' unloaded.");
                break;

            case 'reload':
                if(plugins()->unloadPlugin($data[1]))
                {
                    if (plugins()->loadPlugin($data[1]))
                    {
                        message($location, "Plugin '{$data[1]}' reloaded.");
                        break;
                    }

                    message($location, "Error loading plugin '{$data[1]}'.");
                }

                message($location, "Error unloading plugin '{$data[1]}'.");

                break;

            case 'list':
                message($location, implode(', ', plugins()->plugins()));
                break;

            case 'create':
                if(plugins()->create($data[1], ['user' => $user->nick()]))
                    message($location, "Plugin created. Start coding!");
                break;

            case 'loaded':
                message($location, implode(', ', plugins()->loaded()));
                break;
        }
    }
    catch(Exception $e)
    {
        message($location, relative($e->getMessage()));
    }
}

if($entry == 'help')
{
    return [
        "{cp}plugin load <name> - Loads <name>.",
        "{cp}plugin unload <name> - unloads <name>.",
        "{cp}plugin loaded - Gets loaded plugins.",
        "{cp}plugin list - Gets all available plugins.",
    ];
}