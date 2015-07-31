<?php namespace Dan\Helpers;

use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Hooks {

    protected static $hooks = [];

    public static function defineHook($data, $callback)
    {
        if(!isset($data['name']))
        {
            controlLog("This must have a defined name.");
            return;
        }

        static::$hooks[$data['name']] = [
            'data'      => $data,
            'callback'  => $callback
        ];

        debug("Registered hook {$data['name']}");
    }

    /**
     * Calls a single hook.
     *
     * @param $name
     * @param $eventData
     * @return bool
     */
    public static function callHook($name, $eventData)
    {
        if(!isset(static::$hooks[$name]))
            return false;

        $hook = static::$hooks[$name];
        $data = $hook['data'];

        if(!isset($data['regex']))
            return false;

        if(!preg_match_all($data['regex'], $eventData['message'], $matches))
            return false;

        $callback = $hook['callback'];
        $return = $callback($eventData, $matches);

        if(empty($return))
            return false;

        foreach((array)$return as $line)
            if(!is_bool($line)) // ignore booleans
                message($eventData['channel'], $line);

        return true;
    }

    /**
     * @param $eventData
     * @return bool
     */
    public static function callHooks($eventData)
    {
        debug("Calling hooks");

        foreach(static::$hooks as $name => $hook)
            if(static::callHook($name, $eventData))
                return true;

        return false;
    }


    /**
     *
     */
    public static function registerHooks()
    {
        debug("Registering hooks");

        static::$hooks = [];

        $files = filesystem()->allFiles(ROOT_DIR . '/hooks');

        foreach($files as $file)
        {
            debug("Registering {$file}");
            include($file);
        }
    }
}