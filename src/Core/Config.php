<?php

namespace Dan\Core;

use Dan\Helpers\DotCollection;
use Exception;
use Illuminate\Support\Arr;

class Config extends DotCollection
{
    /** @var static[]  */
    protected static $configs = [];

    protected $name;
    protected $file;

    /**
     * @param array|mixed $name
     * @param array       $data
     *
     * @throws \Exception
     */
    public function __construct($name, $data = [])
    {
        parent::__construct($data);

        $this->name = $name;
        $this->file = CONFIG_DIR.'/'.$this->name.'.json';

        if (filesystem()->exists($this->file)) {
            $json = json_decode(filesystem()->get($this->file), true);

            if (!is_array($json)) {
                throw new Exception("Error loading JSON for '{$name}.json'. Please check and correct the file.");
            }

            $this->items = $json;
        }

        static::$configs[$name] = $this;
    }

    /**
     * Saves the config.
     */
    public function save()
    {
        filesystem()->put(CONFIG_DIR.'/'.$this->name.'.json', json_encode($this->items, JSON_PRETTY_PRINT));
    }

    /**
     * Saves all configs.
     */
    public static function saveAll()
    {
        foreach (static::$configs as $config) {
            $config->save();
        }
    }

    /**
     * Load all config files.
     */
    public static function load()
    {
        static::$configs = [];

        foreach (filesystem()->glob(CONFIG_DIR.'/*.json') as $file) {
            $name = basename($file, '.json');
            new self($name);
        }
    }

    /**
     * Creates a new config.
     *
     * @param $name
     * @param $default
     *
     * @return Config
     */
    public static function create($name, $default)
    {
        if (filesystem()->exists(CONFIG_DIR.'/'.$name.'.json')) {
            return static::$configs[$name];
        }

        return new static($name, $default);
    }

    /**
     * Gets a config value by dot notation. Returns NULL if the config isn't found.
     *
     * @param mixed $name
     *
     * @return Config|mixed
     */
    public static function fetchByKey($name)
    {
        $item = explode('.', $name, 2);

        if (array_key_exists($item[0], static::$configs)) {
            if (count($item) > 1) {
                return static::$configs[$item[0]]->get($item[1]);
            }

            return static::$configs[$item[0]];
        }

        return;
    }

    /**
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $item = explode('.', $key, 2);

        if (array_key_exists($item[0], static::$configs)) {
            if (count($item) > 1) {
                static::$configs[$item[0]]->set($item[1], $value);
            }
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public static function add($key, $value)
    {
        $configs = explode('.', $key, 2);

        $config = static::$configs[$configs[0]];

        $items = Arr::get($config, $configs[1], []);

        $items[] = $value;

        Arr::set(static::$configs[$configs[0]], $configs[1], $items);
    }

    /**
     * @param $key
     * @param $value
     */
    public static function remove($key, $value)
    {
        $configs = explode('.', $key, 2);

        $config = static::$configs[$configs[0]];

        $items = Arr::get($config, $configs[1], []);

        foreach ($items as $i => $item) {
            if ($item == $value) {
                unset($items[$i]);
            }
        }

        Arr::set(static::$configs[$configs[0]], $configs[1], array_values($items));
    }
}
