<?php

namespace Dan\Hooks;

use Dan\Contracts\MessagingContract;
use Dan\Core\Dan;
use Dan\Hooks\Types\CommandHook;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\Location;
use Dan\Irc\Location\User;
use Dan\Web\Response;

class HookManager
{
    /**
     * @var Hook[]
     */
    protected static $hooks = [];

    /**
     * @var array
     */
    protected $except = [];

    /**
     * @var array
     */
    protected $args = [];

    /**
     * HookManager constructor.
     *
     * @param $args
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Calls all hooks except the ones given.
     *
     * @param $items
     *
     * @return $this
     */
    public function except($items)
    {
        $this->except = (array) $items;

        return $this;
    }

    /**
     * Calls the hooks by type.
     *
     * @param $name
     *
     * @return bool
     */
    public function call($name)
    {
        try {
            $name = 'call'.ucfirst(strtolower($name)).'Hooks';

            if (method_exists($this, $name)) {
                return $this->$name($this->args);
            } elseif (DEBUG) {
                error("Method {$name} doesn't exist.");
            }
        } catch (\Error $error) {
            error($error->getMessage());

            if (DEBUG) {
                error($error->getFile().':'.$error->getLine());
            }

            if (isset($this->args['channel']) && $this->args['channel'] instanceof Location) {
                $this->args['channel']->message('Something unexpected has happened!');
            }

            return false;
        }
    }

    /**
     * @return bool
     */
    public function callCommandHooks($args)
    {
        $prefix = connection()->config->get('command_prefix');
        $command = explode(' ', $args['message'], 2);

        if (strpos($command[0], $prefix) !== 0) {
            return false;
        }

        $name = substr($command[0], strlen($prefix));
        $args['message'] = count($command) > 1 ? $command[1] : null;

        foreach (static::getHooks('command', $this->except) as $hook) {
            if ($this->runCommandHook($hook, $name, $args)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $args
     *
     * @return bool
     */
    public function callRegexHooks($args)
    {
        foreach (static::getHooks('regex', $this->except) as $hook) {
            if ($this->runRegexHook($hook, $args)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Runs all the http hooks.
     *
     * @param $args
     *
     * @return bool
     */
    public function callHttpHooks($args)
    {
        foreach (static::getHooks('http', $this->except) as $hook) {
            $value = $this->runHttpHook($hook, $args);

            if ($value instanceof Response || $value === true) {
                return $value;
            }

            if ($value === null) {
                return response();
            }
        }

        return response('404 Route Not Found', 404);
    }

    /**
     * @param \Dan\Hooks\Hook $hook
     * @param $args
     *
     * @return bool
     */
    public function runRegexHook(Hook $hook, $args)
    {
        try {
            return $hook->hook()->run($args);
        } catch (\Error $error) {
            error($error->getMessage());

            if (DEBUG) {
                error($error->getFile().':'.$error->getLine());
            }

            if (isset($this->args['channel']) && $this->args['channel'] instanceof Location) {
                $this->args['channel']->message('Something unexpected has happened!');
            }

            return false;
        }
    }

    /**
     * @param \Dan\Hooks\Hook $hook
     * @param $args
     *
     * @return bool
     */
    public function runCommandHook(Hook $hook, $name, $args)
    {
        /** @var CommandHook $command */
        $command = $hook->hook();

        if (!in_array($name, $command->commands)) {
            return false;
        }

        /** @var Channel $channel */
        $channel = $args['channel'];

        /** @var User $user */
        $user = $args['user'];

        $console = isset($args['console']) && $args['console'];

        if (!$console) {
            if (!$this->hasPermission($command, $user)) {
                $channel->message("You can't use this command!");

                return true;
            }

            controlLog("{$user->nick()} used {$command->commands[0]} in {$channel->getLocation()}");
        }

        try {
            $command->run($args);
        } catch (\Error $error) {
            error($error->getMessage());

            if (DEBUG) {
                error($error->getFile().':'.$error->getLine());
            }

            if (isset($this->args['channel']) && $this->args['channel'] instanceof MessagingContract) {
                $this->args['channel']->message('Something unexpected has happened!');
            }
        }

        return true;
    }

    /**
     * Runs the HTTP hook.
     *
     * @param \Dan\Hooks\Hook $hook
     * @param $args
     *
     * @return bool|void
     */
    public function runHttpHook(Hook $hook, $args)
    {
        return $hook->hook()->run($args);
    }

    /**
     * @param \Dan\Hooks\Types\CommandHook $command
     * @param \Dan\Irc\Location\User       $user
     *
     * @return bool
     */
    protected function hasPermission(CommandHook $command, User $user)
    {
        if (Dan::isOwner($user)) {
            return true;
        }

        $rank = $this->getRank($command);

        if (strpos($rank, 'A') !== false) {
            if (Dan::isAdmin($user)) {
                return true;
            }
        }

        return $user->hasOneOf($rank);
    }

    /**
     * @param \Dan\Hooks\Types\CommandHook $command
     *
     * @return array|\Dan\Core\Config|mixed|string
     */
    protected function getRank(CommandHook $command)
    {
        $ranks = config('commands.permissions');
        $commands = $command->commands;

        foreach ($commands as $cmd) {
            if (array_key_exists($cmd, $ranks)) {
                return $ranks[$cmd];
            }
        }

        if ($command->rank != null) {
            return $command->rank;
        }

        return config('commands.default_permissions');
    }

    /**
     * Sets hook data.
     *
     * @param $args
     *
     * @return $this
     */
    public static function data($args)
    {
        return new self($args);
    }

    /**
     * Loads all hooks.
     */
    public static function loadHooks()
    {
        foreach (static::getHooks('event') as $hook) {
            foreach ($hook->hook()->events() as $event) {
                $event->destroy();
            }
        }

        static::$hooks = [];

        foreach (filesystem()->allFiles(HOOK_DIR) as $file) {
            // This hook was disabled, ignore it.
            if (strpos(basename($file), '_') === 0) {
                continue;
            }

            try {
                include $file;
            } catch (\Error $error) {
                error($error->getMessage());
            }
        }
    }

    /**
     * @param $name
     *
     * @return \Dan\Hooks\Hook
     */
    public static function registerHook($name) : Hook
    {
        static::$hooks[$name] = new Hook($name);

        success("Loaded hook {$name}");

        return static::$hooks[$name];
    }

    /**
     * @param string $type
     * @param array  $except
     *
     * @return \Dan\Hooks\Hook[]
     */
    public static function getHooks($type = null, $except = [])
    {
        $hooks = static::$hooks;

        foreach ($except as $e) {
            unset($hooks[$e]);
        }

        if ($type == null) {
            return $hooks;
        }

        foreach ($hooks as $name => $hook) {
            if ($hook->getType() != $type) {
                unset($hooks[$name]);
            }
        }

        ksort($hooks);

        return $hooks;
    }
}
