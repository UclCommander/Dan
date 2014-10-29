<?php namespace Dan\Irc; 


class Support {

    protected static $support = [];

    /**
     * Gets a support item. If it cannot be found it returns false.
     *
     * @param $name
     * @return bool|string
     */
    public static function get($name)
    {
        return array_key_exists($name, static::$support) ? static::$support[$name] : false;
    }

    /**
     * Puts a support item.
     *
     * @param $name
     * @param $value
     */
    public static function put($name, $value)
    {
        static::$support[$name] = $value;
    }
}
 