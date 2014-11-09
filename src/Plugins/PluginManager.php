<?php namespace Dan\Plugins; 

use Dan\Core\Console;
use Dan\Exceptions\ClassLoadException;
use Dan\Exceptions\PluginDoesNotExistException;

class PluginManager {

    protected $loaded = [];

    private $tempDir = null;

    public function __construct()
    {
        $this->tempDir = STORAGE_DIR . '/plugins/';

        if(!file_exists($this->tempDir))
            mkdir($this->tempDir);
    }

    /**
     * Loads a plugin
     *
     * @param $name
     * @throws \Dan\Exceptions\ClassLoadException
     * @throws \Dan\Exceptions\PluginDoesNotExistException
     */
    public function loadPlugin($name)
    {
        //normalize the name
        $name = ucfirst(strtolower($name));

        Console::text("Attempting to load plugin {$name}..")->debug()->info()->push();

        if(!$this->pluginExists($name))
        {
            Console::text("Plugin {$name} does not exist -- throwing exception")->debug()->info()->push();
            throw new PluginDoesNotExistException("Plugin $name does not exist");
        }

        $pluginDir = PLUGIN_DIR . "/{$name}/";

        Console::text("Loading config for plugin {$name}")->debug()->info()->push();
        $config = require($pluginDir . 'config.inc');

        //random load key
        //Add "P" to make sure it always starts with a letter, else errors will throw like crazy
        $key = "P" . md5(time().$name.microtime());

        Console::text("Generated one time key for plugin {$name}: {$key}")->debug()->info()->push();

        //ONLY load files that are given
        foreach($config['files'] as $loadable)
        {
            Console::text("Loading plugin file {$loadable} for plugin {$name}")->debug()->info()->push();

            $path = $pluginDir . $loadable;

            $file = file_get_contents($path);
            $file = str_replace([
                "<?php",
                "namespace Plugins\\",
                "use Plugins\\",
                "\\Plugins\\{$name}\\",
                "Plugins\\{$name}\\",
                "Plugins\\\\{$name}\\\\",
            ], [
                "",
                "namespace Plugins\\{$key}\\",
                "use Plugins\\{$key}\\",
                "\\Plugins\\{$key}\\{$name}\\",
                "Plugins\\{$key}\\{$name}\\",
                "Plugins\\{$key}\\{$name}\\",
            ], $file);

            Console::text("Running inline eval for file {$loadable} in plugin {$name}")->debug()->info()->push();

            eval($file);

            $className  = str_replace(['.php', '/'], ['', '\\'], $loadable);
            $class      = "Plugins\\{$key}\\{$name}\\{$className}";

            Console::text("Initializing {$class} for plugin {$name}")->debug()->info()->push();

            if(!interface_exists($class)) //ignore interfaces
            {
                if (!class_exists($class))
                {
                    Console::text("Error loading {$class} for plugin {$name}")->debug()->info()->push();
                    throw new ClassLoadException("Error loading {$class} for plugin {$name}");
                }

                /** @var \Dan\Contracts\PluginContract $plugin */
                $plugin =  new $class;

                if(in_array('Dan\Contracts\PluginContract', class_implements($plugin)))
                {
                    Console::text("Class {$class} extends PluginContract, registering plugin file for {$name}")->debug()->info()->push();
                    $plugin->register();
                }

                Console::text("Plugin file {$loadable} loaded for plugin {$name}, adding class to cache array")->debug()->info()->push();
                $this->loaded[strtolower($name)][$key][$path] = $plugin;
            }
        }
    }


    public function unloadPlugin($name)
    {
        //clean up name
        $name = strtolower($name);

        Console::text("Unloading plugin {$name}...")->debug()->alert()->push();

        if(!array_key_exists($name, $this->loaded))
        {
            Console::text("Unable to find loaded plugin {$name}")->debug()->alert()->push();
            return;
        }

        foreach($this->loaded[$name] as $id => $paths)
        {
            foreach($paths as $class)
            {
                /** @var \Dan\Contracts\PluginContract $class */

                Console::text("Unloading " . get_class($class) . " for plugin {$name}")->debug()->info()->push();

                if (in_array('Dan\Contracts\PluginContract', class_implements($class)))
                {
                    Console::text("Unregistering plugin entry point for plugin {$name}")->debug()->info()->push();
                    $class->unregister();
                }

                unset($class);
            }
        }

        unset($this->loaded[$name]);
        Console::text("Plugin {$name} unloaded")->debug()->success()->push();
    }

    /**
     * Checks to see if a plugin exists
     *
     * @param $name
     * @return bool
     */
    public function pluginExists($name)
    {
        return file_exists(PLUGIN_DIR . "/{$name}/{$name}.php");
    }
}

