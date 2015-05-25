<?php

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

/** @var User $user */
/** @var Channel $channel */
/** @var string $message */
/** @var string $entry */

if($entry == 'use')
{
    $data = explode(' ', $message);

    try
    {
        switch ($data[0])
        {
            case 'load':
                if(plugins()->loadPlugin($data[1]))
                    message($channel, "Plugin '{$data[1]}' loaded.");
                break;

            case 'unload':
                if(plugins()->unloadPlugin($data[1]))
                    message($channel, "Plugin '{$data[1]}' unloaded.");
                break;

            case 'reload':
                if(plugins()->unloadPlugin($data[1]))
                {
                    if (plugins()->loadPlugin($data[1]))
                    {
                        message($channel, "Plugin '{$data[1]}' reloaded.");
                        break;
                    }

                    message($channel, "Error loading plugin '{$data[1]}'.");
                }

                message($channel, "Error unloading plugin '{$data[1]}'.");

                break;

            case 'list':
                message($channel, implode(', ', plugins()->plugins()));
                break;

            case 'create':
                if(plugins()->create($data[1], ['user' => $user->nick()]))
                    message($channel, "Plugin created. Start coding!");
                break;

            case 'loaded':
                message($channel, implode(', ', plugins()->loaded()));
                break;
        }
    }
    catch(Exception $e)
    {
        message($channel, relative($e->getMessage()));
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