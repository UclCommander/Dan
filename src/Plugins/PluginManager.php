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
            ], [
                "",
                "namespace Plugins\\{$key}\\",
                "use Plugins\\{$key}\\",
            ], $file);

            Console::text("Running inline eval for file {$loadable} in plugin {$name}")->debug()->info()->push();

            eval($file);

            $className = basename($loadable, '.php');
            $class = "Plugins\\{$key}\\{$name}\\{$className}";

            Console::text("Initializing {$class} for plugin {$name}")->debug()->info()->push();

            if(!class_exists($class))
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
            $this->loaded[$name][$key][$path] = $plugin;
        }
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

