<?php namespace Dan\Core;


class Config {

    private static $config = [];

    public static function load()
    {
        $temp = [];

        foreach(glob(CONFIG_DIR . '/*.php') as $file)
        {
            $name = basename($file, '.php');
            $temp[$name] = include($file);
        }

        return static::$config = $temp;
    }

    /**
     * Gets an item from the config.
     *
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        $item = static::$config;

        foreach(explode('.', $key) as $k)
        {
            if(!array_key_exists($k, $item))
                return null;

            $item = $item[$k];
        }

        return $item;
    }
}
 