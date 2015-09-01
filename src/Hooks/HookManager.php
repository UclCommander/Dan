<?php namespace Dan\Hooks;

use Dan\Core\Dan;
use Dan\Hooks\Types\CommandHook;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

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

        /** @var Channel $channel */
        $channel = $args['channel'];
        /** @var User $user */
        $user = $args['user'];

        if(!static::hasPermission($command, $user))
        {
            $channel->message("You can't use this command!");
            return true;
        }

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

    /**
     * @param \Dan\Hooks\Types\CommandHook $command
     * @param \Dan\Irc\Location\User $user
     * @return bool
     */
    protected static function hasPermission(CommandHook $command, User $user)
    {
        if(Dan::isOwner($user))
            return true;

        $rank = static::getRank($command);

        if(strpos($rank, 'A') !== false)
            if(Dan::isAdmin($user))
                return true;

        return $user->hasOneOf($rank);
    }

    /**
     * @param \Dan\Hooks\Types\CommandHook $command
     * @return array|\Dan\Core\Config|mixed|string
     */
    protected static function getRank(CommandHook $command)
    {
        $ranks      = config("commands.permissions");
        $commands   = $command->commands;

        foreach($commands as $cmd)
            if(array_key_exists($cmd, $ranks))
                return $ranks[$cmd];

        if($command->rank != null)
            return $command->rank;

        return config('commands.default_permissions');
    }
}