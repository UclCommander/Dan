<?php namespace Dan\Plugins; 

use Dan\Core\Dan;
use Exception;
use Illuminate\Support\Collection;
use \Phar;

class PluginManager {

    /** @var Collection $plugins */
    protected $plugins = [];

    protected $map = [];


    public function __construct()
    {
        $this->plugins = new Collection();

        filesystem()->cleanDirectory(PLUGIN_STORAGE);
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
        return array_unique(array_map(function($d) { return basename($d, '.phar'); }, glob(PLUGIN_DIR . '/*')));
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

        if(commandExists('box') && DEBUG)
            $this->buildPlugin($realname);

        filesystem()->copy($phar, PLUGIN_STORAGE ."/{$hased}");

        if(!Phar::loadPhar(PLUGIN_STORAGE ."/{$hased}", $hased))
            throw new Exception("Error loading plugin {$realname}");

        $json       = file_get_contents("phar://{$hased}/config.json");
        $config     = json_decode($json, true);

        if($config == null)
            throw new Exception("Error loading config for {$realname}.");

        if(!version_compare(Dan::VERSION, $config['requires'], '>='))
            throw new Exception("Plugin {$realname} requires version {$config['requires']} or later.");

        $loadDir    = "phar://{$hased}/src/";
        $namespace  = $config['namespace']."\\";
        $class      = $namespace.$config['class'];

        Dan::composer()->addPsr4($namespace, $loadDir);

        if(!class_exists($class, true))
            throw new Exception("Error loading '{$realname}' - class '{$class}' doesn't exist.");

        /** @var Plugin $object */
        $object = new $class();

        $object->load();

        $this->plugins->put($realname, $object);

        $this->map[$realname] = $hased;

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

        $hashed = $this->map[$plugin];

        Phar::unlinkArchive(PLUGIN_STORAGE . "/{$hashed}");

        unset($this->map[$plugin]);

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
}