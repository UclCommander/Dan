<?php namespace Dan\Core;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Config extends Collection {

    /** @var static[]  */
    protected static $configs = [];

    protected $name;
    protected $file;

    /**
     * @param array|mixed $name
     * @param array $data
     * @throws \Exception
     */
    public function __construct($name, $data = [])
    {
        parent::__construct($data);

        $this->name = $name;
        $this->file = CONFIG_DIR . '/' . $this->name . '.json';

        if(filesystem()->exists($this->file))
        {
            $json = json_decode(filesystem()->get($this->file), true);

            if(!is_array($json))
                throw new Exception("Error loading JSON for {$name}. Please check and correct the file.");

            $this->items = $json;
        }

        static::$configs[$name] = $this;
    }

    /**
     * Saves the config.
     */
    public function save()
    {
        Dan::filesystem()->put(CONFIG_DIR . '/' . $this->name . '.json', json_encode($this->items, JSON_PRETTY_PRINT));
    }

    /**
     * Gets an item using dot notation.
     *
     * @param mixed $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if(Arr::has($this->items, $key))
            return Arr::get($this->items, $key);

        return $default;
    }

    /**
     * Puts a value only if it doesn't exist with dot notation
     *
     * @param $key
     * @param $value
     * @return bool|null
     */
    public function putIfNull($key, $value)
    {
        if(Arr::has($this->items, $key))
            return null;

        Arr::set($this->items, $key, $value);

        return true;
    }

    /**
     * Gets a config value by dot notation. Returns NULL if the config isn't found.
     *
     * @param mixed $name
     * @return Config|mixed
     */
    public static function fetchByKey($name)
    {
        $item = explode('.', $name, 2);

        if(array_key_exists($item[0], static::$configs))
        {
            if(count($item) > 1)
                return static::$configs[$item[0]]->get($item[1]);

            return static::$configs[$item[0]];
        }

        return null;
    }

    /**
     * Load all config files.
     */
    public static function load()
    {
        static::$configs = [];

        foreach(filesystem()->glob(CONFIG_DIR . '/*.json') as $file)
        {
            $name = basename($file, '.json');
            new Config($name);
        }
    }
}