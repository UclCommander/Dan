<?php

namespace Dan\Plugins;


use Dan\Core\Dan;
use Exception;
use Illuminate\Support\Collection;
use Phar;

class PluginManager {

    /** @var Collection %plugins */
    protected $plugins;

    protected $autoLoader;
    protected $map = [];


    public function __construct()
    {
        $this->plugins = new Collection();

        $this->autoLoader = new PluginAutoloader();

        filesystem()->cleanDirectory(PLUGIN_STORAGE);
    }

    /**
     * Loads a plugin.
     *
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function loadPlugin($name)
    {
        $plugin = $this->getPluginName($name);

        if($plugin === false)
            throw new Exception("Plugin {$name} doesn't exist.");

        if($this->plugins->has($plugin))
            throw new Exception("Plugin '{$plugin}' already loaded.");

        if(event("dan.plugins.loading", ['plugin' => $plugin]) === false)
            return false;

        $hash       = md5(microtime() . $plugin);
        $hashed     = "{$plugin}{$hash}.phar";
        $pharPath   = PLUGIN_DIR . "/{$plugin}.phar";

        if(commandExists('box') && DEBUG)
            $this->buildPlugin($plugin);

        filesystem()->copy($pharPath, PLUGIN_STORAGE ."/{$hashed}");

        if($plugin === false)
            throw new Exception("Plugin {$name} doesn't exist.");

        if($this->plugins->has($plugin))
            throw new Exception("Plugin '{$plugin}' already loaded.");

        if(event("dan.plugins.loading", ['plugin' => $plugin]) === false)
            return false;

        filesystem()->copy($pharPath, PLUGIN_STORAGE ."/{$hashed}");

        if(!Phar::loadPhar(PLUGIN_STORAGE ."/{$hashed}", $hashed))
            throw new Exception("Error loading plugin {$plugin}");

        $json       = file_get_contents("phar://{$hashed}/config.json");
        $config     = json_decode($json, true);

        if($config == null)
            throw new Exception("Error loading config for {$plugin}.");

        if(!version_compare(Dan::VERSION, $config['requires'], '>='))
            throw new Exception("Plugin {$plugin} requires version {$config['requires']} or later.");

        $loadDir    = "phar://{$hashed}/src/";

        $namespace  = "{$config['namespace']}{$hash}\\";
        $class      = $namespace.$config['class'];

        $this->autoLoader->registerPlugin($plugin, $loadDir, $hash);

        if(!class_exists($class))
            throw new Exception("Error loading '{$plugin}' - class '{$class}' doesn't exist.");

        /** @var Plugin $object */
        $object = new $class();
        $object->load();

        $this->plugins->put($plugin, $object);
        $this->map[$plugin] = $hashed;

        return true;
    }

    /**
     * Unloads all plugins.
     *
     * @throws \Exception
     */
    public function unloadAll()
    {
        foreach($this->plugins->keys()->toArray() as $plugin)
            $this->unloadPlugin($plugin);
    }

    /**
     * Unloads a plugin.
     *
     * @param $name
     * @return bool
     * @throws \Exception
     */
    public function unloadPlugin($name)
    {
        $plugin = $this->getPluginName($name);

        if(event("dan.plugins.unloading", ['plugin' => $plugin]) === false)
            return false;

        if(!$this->plugins->has($plugin))
            throw new Exception("Plugin '{$name}' is not loaded.");

        $this->plugins->get($plugin)->unload();
        $this->plugins->forget($plugin);

        $hashed = $this->map[$plugin];

        unset($this->map[$plugin]);

        $this->autoLoader->unregisterPlugin($plugin);

        if(!Phar::unlinkArchive(PLUGIN_STORAGE . "/{$hashed}"))
            throw new Exception("Error unlinking PHAR.");

        event('dan.plugins.unloaded', ['plugin' => $plugin]);

        return true;
    }

    /**
     * Creates a plugin.
     *
     * @param $plugin
     * @param array $info
     * @return bool
     * @throws \Exception
     */
    public function create($plugin, $info = [])
    {
        $path = PLUGIN_DIR . "/{$plugin}";

        if(filesystem()->exists($path))
            throw new Exception("Plugin {$plugin} already exists");

        filesystem()->makeDirectory("{$path}/src", 0775, true);

        filesystem()->put($path . '/config.json', json_encode([
            "name"      => $plugin,
            "version"   => "1.0",
            "author"    => $info['user'],
            "requires"  => Dan::VERSION,
            "namespace" => $plugin,
            "class"     => $plugin
        ], JSON_PRETTY_PRINT));

        filesystem()->put($path . '/box.json', json_encode([
            "files" => ["config.json"],
            "directories" => ["src"],
            "output" => "../{$plugin}.phar",
            "stub" => true
        ], JSON_PRETTY_PRINT));

        $php = <<<PHP
<?php namespace {$plugin};

use Dan\Plugins\Plugin;

class {$plugin} extends Plugin {

    /**
     * Loading function, called when the plugin is loaded
     */
    public function load()
    {
        controlLog("{$plugin} loaded!");
    }

    /**
     * Unloading function, called when the plugin is unloaded.
     * Be sure parent::unload(); is always inside of this function,
     * It removes all event bindings from the plugin.
     */
    public function unload()
    {
        parent::unload();

        controlLog("{$plugin} unloaded!");
    }
}
PHP;

        filesystem()->put($path . "/src/{$plugin}.php", $php);

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

    /**
     * Builds a plugin using Box2.
     *
     * @param $plugin
     * @return bool|void
     */
    public function buildPlugin($plugin)
    {
        $dir = PLUGIN_DIR . "/{$plugin}";

        if(!filesystem()->exists("{$dir}/box.json"))
            return false;

        controlLog("Building plugin {$plugin}..");

        $output = shell_exec("cd {$dir} && box build");

        controlLog(trim($output));

        return ($output == 'Building...');
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
     * Gets all available plugins.
     *
     * @return array
     */
    public function plugins()
    {
        return array_unique(array_map(function($d) { return basename($d, '.phar'); }, glob(PLUGIN_DIR . '/*')));
    }

}