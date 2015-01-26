<?php namespace Dan\Core;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Config {

    /** @var array[] $config */
    private static $config = [];

    /** @var Filesystem $filesystem */
    protected static $filesystem;

    public static function load()
    {
        static::$config = [];

        if(static::$filesystem == null)
            static::$filesystem = new Filesystem();

        foreach(static::$filesystem->glob(CONFIG_DIR . '/*.json') as $file)
        {
            $name = basename($file, '.json');
            static::$config[$name] = json_decode(static::$filesystem->get($file), true);
        }
    }

    /**
     * Saves config as JSON
     */
    public static function save()
    {
        foreach(static::$config as $file => $config)
            static::$filesystem->put(CONFIG_DIR . "/{$file}.json", json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Gets an item from the config.
     *
     * @param $key
     * @return Collection|string
     */
    public static function get($key)
    {
        $data = Arr::get(static::$config, $key);

        if(is_array($data))
            return new Collection($data);

        return $data;
    }

    /**
     * Sets an item into the config.
     *
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        Arr::set(static::$config, $key, $value);
    }

    /**
     * Sets an item into the config.
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public static function add($key, $value)
    {
        if(!is_array(Arr::get(static::$config, $key)))
            return false;

        Arr::add(static::$config, $key, $value);
        return true;
    }
}
 