<?php namespace Dan\Core;


use Illuminate\Support\Arr;

class Config {

    /** @var array[] $config */
    private static $config = [];

    public static function load()
    {
        $temp = [];

        foreach(glob(CONFIG_DIR . '/*.php') as $file)
        {
            $name = basename($file, '.php');
            $temp[$name] = include($file);
        }

        static::$config = $temp;
    }

    /**
     * Gets an item from the config.
     *
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return Arr::get(static::$config, $key);
    }

    /**
     * Sets an item into the config.
     *
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        static::$config = Arr::set(static::$config, $key, $value);
    }
}
 