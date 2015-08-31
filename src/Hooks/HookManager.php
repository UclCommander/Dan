<?php namespace Dan\Hooks;

use Dan\Hooks\Types\CommandHook;
use Dan\Hooks\Types\EventHook;

class HookManager {

    /**
     * @var Hook[] $hooks
     */
    protected static $hooks = [];

    /**
     *
     */
    public static function loadHooks()
    {
        foreach(static::getHooks('event') as $hook)
            $hook->hook()->event()->destroy();

        static::$hooks = [];

        foreach(filesystem()->allFiles(HOOK_DIR) as $file)
        {
            try
            {
                include($file);
            }
            catch(\Error $error)
            {
                error($error->getMessage());
            }
        }
    }

    /**
     * @param $name
     * @return \Dan\Hooks\Hook
     */
    public static function registerHook($name) : Hook
    {
        static::$hooks[$name] = new Hook($name);

        return static::$hooks[$name];
    }

    /**
     * @param $args
     * @return bool
     */
    public static function callRegexHooks($args)
    {
        foreach(static::getHooks('regex') as $hook)
            if(static::runRegexHook($hook, $args))
                return true;

        return false;
    }

    /**
     * @param $args
     * @return bool
     */
    public static function callCommandHooks($args)
    {
        $prefix = connection()->config->get('command_prefix');
        $command = explode(' ', $args['message'], 2);

        if(strpos($command[0], $prefix) !== 0)
            return false;

        $name = substr($command[0], strlen($prefix));
        $args['message'] = count($command) > 1 ? $command[1] : null;

        foreach(static::getHooks('command') as $hook)
            if(static::runCommandHook($hook, $name, $args))
                return true;

        return false;
    }

    /**
     * @param $type
     * @param array $data
     * @return bool
     */
    public static function callHooks($type, array $data){}

    /**
     * @param string $type
     * @return \Dan\Hooks\Hook[]
     */
    public static function getHooks($type = null)
    {
        if($type == null)
            return static::$hooks;

        $hooks = [];

        foreach(static::$hooks as $hook)
            if($hook->getType() == $type)
                $hooks[] = $hook;

        return $hooks;
    }

    /**
     * @param \Dan\Hooks\Hook $hook
     * @param $args
     */
    public static function runRegexHook(Hook $hook, $args)
    {
        try
        {
            return $hook->hook()->run($args);
        }
        catch(\Error $error)
        {
            $args['channel']->message("Something unexpected has happened!");
            error($error->getMessage());

            return false;
        }
    }

    /**
     * @param \Dan\Hooks\Hook $hook
     * @param $args
     * @return bool
     */
    public static function runCommandHook(Hook $hook, $name, $args)
    {
        /** @var CommandHook $command */
        $command = $hook->hook();

        if(!in_array($name, $command->commands))
            return false;

        try
        {
            $command->run($args);
        }
        catch(\Error $error)
        {
            $args['channel']->message("Something unexpected has happened!");
            error($error->getMessage());
        }

        return true;
    }
}