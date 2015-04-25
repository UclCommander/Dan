<?php namespace Dan\Plugins; 

use Dan\Core\Console;
use Dan\Exceptions\PluginDoesNotExistException;
use Dan\Exceptions\PluginIsNotLoadedException;
use Dan\Exceptions\RequiredPluginNeedsToBeLoadedException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class PluginManager {

    /** @var \Illuminate\Support\Collection $classMap */
    protected $classMap;

    /** @var \Illuminate\Support\Collection $loaded */
    protected $loaded;

    protected $required = [];

    /** @var string $storageDir */
    protected $storageDir;

    /** @var string $loadingDir */
    protected $loadingDir;

    /** @var \Illuminate\Filesystem\Filesystem $filesystem */
    protected $filesystem;

    /**
     * Plugin manager class.
     */
    public function __construct()
    {
        $this->filesystem   = new Filesystem();
        $this->classMap     = new Collection();
        $this->loaded       = new Collection();
        $this->storageDir   = STORAGE_DIR . '/plugins/';
        $this->loadingDir   = '';

        // Clear out the plugins directory
        $this->filesystem->cleanDirectory($this->storageDir);
    }

    /**
     * Gets the loaded plugins.
     *
     * @return array
     */
    public function loaded()
    {
        return array_keys($this->loaded->toArray());
    }


    /**
     * Gets all available plugins.
     *
     * @return array
     */
    public function all()
    {
        $plugins = [];

        foreach(Finder::create()->in(PLUGIN_DIR)->directories()->depth(0) as $dir)
            $plugins[] = $dir->getFilename();

        return $plugins;
    }

    /**
     * @param $name
     * @return bool
     */
    public function pluginLoaded($name)
    {
        $name = $this->getPluginName($name);
        return $this->loaded->has($name);
    }

    /**
     * Loads a plugin.
     *
     * @param $name
     * @return bool
     * @throws \Dan\Exceptions\PluginDoesNotExistException
     * @throws \Dan\Exceptions\RequiredPluginNeedsToBeLoadedException
     */
    public function loadPlugin($name)
    {
        $name = $this->getPluginName($name);

        Console::text("Loading plugin {$name}")->info()->push();

        if(!$this->pluginExists($name))
            throw new PluginDoesNotExistException("Plugin $name does not exist");

        $this->loadingDir = PLUGIN_DIR . "/{$name}/";

        $config = $this->loadConfig($name);

        $key = "{$name}_" . $this->generateKey($name);

        $this->copyPluginFiles($name, $config, $key);
        $this->initializePlugin($name, $config, $key);

        $this->loadingDir = '';

        Console::text("Plugin {$name} loaded")->success()->push();

        return true;
    }

    /**
     * Unloads a plugin.
     *
     * @param string $name
     * @throws \Dan\Exceptions\PluginIsNotLoadedException
     */
    public function unloadPlugin($name)
    {
        //clean up name
        $name = $this->getPluginName($name);

        if(!$this->pluginLoaded($name))
            throw new PluginIsNotLoadedException($name);

        /** @var \Dan\Contracts\PluginContract|\Dan\Plugins\Plugin $plugin */
        $plugin = $this->loaded->get($name);

        $key = $plugin->getKey();
        $plugin->unregister();

        $this->loaded->forget($name);
        $this->classMap->forget("Plugins\\{$name}");

        $this->filesystem->deleteDirectory($this->storageDir.$key);
    }

    /**
     * Checks to see if a plugin exists
     *
     * @param $name
     * @return bool
     */
    public function pluginExists($name)
    {
        $name = $this->getPluginName($name);

        return $this->filesystem->exists(PLUGIN_DIR . "/{$name}/{$name}.php");
    }


    /**
     * Recursively scans a directory and returns a one dimensional array of all files with their paths.
     *
     * @param string $scan
     * @param string $prepend
     * @return array
     */
    public function recursiveScan($scan, $prepend = '')
    {
        $result = [];

        $pluginDir = array_diff(scandir($scan), ['..', '.']);

        foreach($pluginDir as $dir)
        {
            if($dir == 'vendor')
                continue;

            if(strpos($dir, '.') === 0 || strpos($dir, 'composer.') === 0)
                continue;


            if(is_dir($scan . $dir))
            {
                $result[] = $this->recursiveScan($scan . $dir.'/', $prepend.$dir.'/');
                continue;
            }

            $result[] = $prepend.$dir;
        }

        return Arr::flatten($result);
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

        $map = array_map(function($d) {
            return basename($d);
        }, glob(PLUGIN_DIR . '/*'));

        preg_match("/{$safe}/i", implode(' ', $map), $match);

        return reset($match);
    }

    /**
     * Generate a key.
     *
     * @param $name
     * @return string
     */
    public function generateKey($name)
    {
        return md5(time().$name.microtime());
    }

    /**
     * Copy all plugin files to a temp directory.
     *
     * @param $name
     * @param $config
     * @param $key
     */
    protected function copyPluginFiles($name, $config, $key)
    {
        foreach($config['files'] as $loadable)
        {
            $dir    = dirname($loadable);
            $temp   = "{$this->storageDir}{$key}/" . ($dir == '.' ? '' : $dir);

            $file   = $this->parsePluginFile($loadable, $name, $key);

            if (!file_exists("{$this->storageDir}{$key}"))
                mkdir("{$this->storageDir}{$key}");

            if (!file_exists($temp))
                mkdir($temp);

            $this->filesystem->put("{$temp}/" . basename($loadable), $file);
        }
    }

    /**
     * Parse a plugin file for dynamic loading.
     *
     * @param $path
     * @param $name
     * @param $key
     * @return mixed|string
     */
    protected function parsePluginFile($path, $name, $key)
    {
        $path = $this->loadingDir . $path;
        $file = $this->filesystem->get($path);

        $arr = $this->classMap->toArray();

        $find = array_keys($arr);

        $keys = array_merge($find, [
            "Plugins\\{$name}",
            "Plugins\\{$name}\\",
        ]);

        $replace = array_values($arr);

        $values = array_merge($replace, [
            "PluginTemp\\{$key}",
            "PluginTemp\\{$key}\\",
        ]);

        $file = str_replace($keys, $values, $file);

        return $file;
    }

    /**
     * Initialize a plugin.
     *
     * @param       $name
     * @param array $config
     * @param       $key
     */
    protected function initializePlugin($name, array $config, $key)
    {
        $files = $this->recursiveScan($this->storageDir . $key);

        // Loop through those files and load them.
        foreach($files as $relative)
        {
            $path = $this->loadingDir . $relative;

            if (is_dir($path))
                continue;

            $file       = basename($path);
            $className  = str_replace(['.php', '/'], ['', '\\'], $file);
            $class      = "PluginTemp\\{$key}\\{$className}";

            $check = new ReflectionClass($class);

            if ($check->isInterface() || $check->isAbstract())
                continue;

            if (!$check->implementsInterface('Dan\Contracts\PluginContract'))
                continue;

            /** @var \Dan\Contracts\PluginContract|\Dan\Plugins\Plugin $plugin */
            $plugin = new $class;
            $plugin->register();
            $plugin->setKey($key);

            $this->loaded->put($name, $plugin);

            $this->classMap->put("Plugins\\{$name}", "PluginTemp\\{$key}");
        }
    }

    /**
     * Loads the config for a plugin
     *
     * @param $name
     * @return array
     * @throws \Dan\Exceptions\RequiredPluginNeedsToBeLoadedException
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    protected function loadConfig($name)
    {
        $config = [];
        $config['required'] = [];

        $inc = [];

        if ($this->filesystem->exists($this->loadingDir . 'config.inc'))
            $inc = $this->filesystem->getRequire($this->loadingDir . 'config.inc');

        $config = array_merge($config, $inc);
        $config['files'] = $this->recursiveScan($this->loadingDir);

        foreach ($config['required'] as $plugin)
        {
            if (!$this->pluginLoaded($plugin))
                throw new RequiredPluginNeedsToBeLoadedException("Plugin '{$plugin}' needs to be loaded for plugin '{$name}' to work.");

            $this->required[$name] = $plugin;
        }

        return $config;
    }
}

