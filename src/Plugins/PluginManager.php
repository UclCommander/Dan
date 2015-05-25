<?php namespace Dan\Plugins; 

use Dan\Core\Dan;
use Exception;
use Illuminate\Support\Collection;
use \Phar;

class PluginManager {

    /** @var array $plugins */
    protected $plugins = [];

    public function __construct()
    {
        $this->plugins = new Collection();
    }

    /**
     * Gets the loaded plugins.
     *
     * @return array
     */
    public function loaded()
    {
        return $this->plugins->keys()->toArray();
    }

    /**
     * Gets all avalible plugins.
     *
     * @return array
     */
    public function plugins()
    {
        return array_map(function($d) { return basename($d, '.phar'); }, glob(PLUGIN_DIR . '/*.phar'));
    }

    /**
     * Loads a plugin.
     *
     * @param $plugin
     * @return bool
     * @throws \Exception
     */
    public function loadPlugin($plugin)
    {
        $realname = $this->getPluginName($plugin);

        if($realname === false)
            throw new Exception("Plugin {$plugin} doesn't exist.");

        if(event("dan.plugins.loading", ['plugin' => $realname]) === false)
            return false;

        if($this->plugins->has($realname))
            throw new Exception("Plugin '{$realname}' already loaded.");

        $hash       = md5(microtime() . $realname);
        $phar       = "{$realname}{$hash}.phar";

        if(!Phar::loadPhar(PLUGIN_DIR . "/{$realname}.phar", $phar))
            throw new Exception("Error loading plugin {$realname}");

        $config     = json_decode(file_get_contents("phar://{$realname}{$hash}.phar/config.json"), true);

        if(!version_compare(Dan::VERSION, $config['requires'], '>='))
            throw new Exception("Plugin {$realname} requires version {$config['requires']} or later.");

        $loadDir    =  "phar://{$phar}/src/";

        $namespace  = $config['namespace']."\\";
        $class      = $namespace.$config['class'];

        Dan::composer()->addPsr4($namespace, $loadDir);

        if(!class_exists($class, true))
            throw new Exception("Error loading '{$realname}' - class '{$class}' doesn't exist.");

        /** @var Plugin $object */
        $object = new $class();

        $object->load();

        $this->plugins->put($realname, $object);

        event('dan.plugins.loaded', ['plugin' => $realname]);
        controlLog("Plugin {$realname} has been loaded.");

        return true;
    }

    /**
     * Unloads a plugin.
     *
     * @param $plugin
     * @return bool
     * @throws \Exception
     */
    public function unloadPlugin($plugin)
    {
        $plugin = $this->getPluginName($plugin);

        if(event("dan.plugins.unloading", ['plugin' => $plugin]) === false)
            return false;

        if(!$this->plugins->has($plugin))
            throw new Exception("Plugin '{$plugin}' is not loaded.");

        $this->plugins->get($plugin)->unload();
        $this->plugins->forget($plugin);

        event('dan.plugins.unloaded', ['plugin' => $plugin]);

        return true;
    }


    /**
     * Gets a plugin name as it is on the file system.
     *
     * @param $name
     * @return string
     */
    public function getPluginName($name)
    {
        $match  = [];
        $safe   = preg_quote($name);

        $map = $this->plugins();

        if(preg_match("/{$safe}/i", implode(' ', $map), $match) === false)
            return false;

        return basename(reset($match));
    }
}