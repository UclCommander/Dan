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
     * Loads a plugin.
     *
     * @param $plugin
     * @return bool
     * @throws \Exception
     */
    public function loadPlugin($plugin)
    {
        $plugin = $this->getPluginName($plugin);

        if(event("dan.plugins.loading", ['plugin' => $plugin]) === false)
            return false;

        if($this->plugins->has($plugin))
            throw new Exception("Plugin '{$plugin}' already loaded.");

        $hash       = md5(microtime() . $plugin);
        $phar       = "{$plugin}{$hash}.phar";

        if(!Phar::loadPhar(PLUGIN_DIR . "/{$plugin}.phar", $phar))
            throw new Exception("Error loading plugin {$plugin}");

        $config     = json_decode(file_get_contents("phar://{$plugin}{$hash}.phar/config.json"), true);
        $loadDir    =  "phar://{$phar}/src/";

        $namespace  = $config['namespace']."\\";
        $class      = $namespace.$config['class'];

        Dan::composer()->addPsr4($namespace, $loadDir);

        if(!class_exists($class, true))
            throw new Exception("Error loading '{$plugin}' - class '{$class}' doesn't exist.");

        /** @var Plugin $object */
        $object = new $class();

        $object->load();

        $this->plugins->put($plugin, $object);

        event('dan.plugins.loaded', ['plugin' => $plugin]);
        controlLog("Plugin {$plugin} has been loaded.");

        return true;
    }

    /**
     * Unloads a plugin.
     *
     * @param $plugin
     * @throws \Exception
     */
    public function unloadPlugin($plugin)
    {
        $plugin = $this->getPluginName($plugin);

        if(event("dan.plugins.unloading", ['plugin' => $plugin]) === false)
            return;

        if(!$this->plugins->has($plugin))
            throw new Exception("Plugin '{$plugin}' is not loaded.");

        $this->plugins->get($plugin)->unload();
        $this->plugins->forget($plugin);

        event('dan.plugins.unloaded', ['plugin' => $plugin]);
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

        $map = array_map(function($d) { return basename($d); }, glob(PLUGIN_DIR . '/*.phar'));

        preg_match("/{$safe}/i", implode(' ', $map), $match);

        return basename(reset($match));
    }
}