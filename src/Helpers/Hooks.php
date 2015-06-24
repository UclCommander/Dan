<?php namespace Dan\Helpers;

use Dan\Events\EventArgs;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Hooks {

    protected static $hooks = [];

    public static function defineHook($data, $callback)
    {
        debug("Defining a hook");

        static::$hooks[] = [
            'data'      => $data,
            'callback'  => $callback
        ];
    }

    /**
     * @param $eventData
     * @return bool
     */
    public static function callHooks($eventData)
    {
        debug("Calling hooks");

        foreach(static::$hooks as $hook)
        {
            $data = $hook['data'];

            if(isset($data['regex']))
            {
                if(preg_match_all($data['regex'], $eventData['message'], $matches))
                {
                    $callback = $hook['callback'];
                    $return = $callback($eventData, $matches);

                    if(!empty($return))
                    {
                        foreach((array)$return as $line)
                            message($eventData['channel'], $line);

                        return true;
                    }
                }
            }
        }

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