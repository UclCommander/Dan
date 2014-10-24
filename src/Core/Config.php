<?php namespace Dan\Core;


class Config {

    private static $config = [];

    public static function load()
    {
        $temp = [];

        foreach(glob(CONFIG_DIR . '*.php') as $file)
        {
            $name = basename($file, '.php');
            $temp[$name] = include($file);
        }


        return static::$config = $temp;
    }
}
 