<?php namespace Dan\Helpers; 


class ColorParser {

    protected static $resetChar = null;

    protected static $char = '';

    protected static $colors = [];

    protected static $fontType = [];

    public static function parse($text)
    {
        $matches = [];
        preg_match_all("/{([a-z_]+)\:?([a-z_]+)?}/", $text, $matches);

        for($i = 0; $i < count($matches[0]); $i++)
        {
            if(empty($matches[1][$i]))
                continue;

            $first  = $matches[1][$i];
            $second = $matches[2][$i];
            $build  = '';

            if($first == 'reset')
                $build = static::$resetChar;

            if(array_key_exists($first, static::$colors))
            {
                $color      = static::$colors[$first];
                $background = (isset(static::$colors[$second]) ? static::$colors[$second] : null);

                $build = static::$char . $color . ($background != null ? ',' . $background : '');
            }

            if(array_key_exists($first, static::$fontType))
            {
                $build = static::$fontType[$first];
            }

            $text = str_replace($matches[0][$i], $build, $text);
        }

        return $text;
    }
}