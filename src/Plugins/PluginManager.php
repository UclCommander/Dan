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
        $hased      = "{$realname}{$hash}.phar";
        $phar       = PLUGIN_DIR . "/{$realname}.phar";

        filesystem()->copy($phar, PLUGIN_STORAGE ."/{$realname}.phar");

        if(!Phar::loadPhar(PLUGIN_STORAGE ."/{$realname}.phar", $hased))
            throw new Exception("Error loading plugin {$realname}");

        $json       = file_get_contents("phar://{$hased}/config.json");

        $config     = json_decode($json, true);

        vd($config, $hash, $hased, $phar, $realname, $plugin, $json);

        if($config == null)
            throw new Exception("Error loading config for {$realname}.");

        if(!version_compare(Dan::VERSION, $config['requires'], '>='))
            throw new Exception("Plugin {$realname} requires version {$config['requires']} or later.");

        $loadDir    =  "phar://{$hased}/src/";

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

        Phar::unlinkArchive(PLUGIN_STORAGE . "/{$plugin}.phar");

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