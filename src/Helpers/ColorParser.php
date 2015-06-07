<?php namespace Dan\Helpers;


class ColorParser {

    protected static $resetChar = null;
    protected static $char = '';
    protected static $colors = [];
    protected static $fontType = [];

    /**
     * @param $text
     * @return string
     */
    public static function parse($text)
    {
        if(strpos($text, '{') === false)
            return $text;

        $items = explode('{', $text);
        $build = [];

        foreach($items as $item)
        {
            $data   = explode('}', $item);
            $colors = explode(',', $data[0]);

            if($data[0] == $item)
            {
                $build[] = $item;
                continue;
            }

            if($colors[0] == 'reset')
            {
                $build[] = static::$resetChar . $data[1];
            }
            else if($colors[0] == 'rainbow')
            {
                $build[] = static::rainbow($data[1]);
            }
            else if($colors[0] == 'random')
            {
                $build[] = static::random($data[1]);
            }
            else if(array_key_exists($colors[0], static::$colors))
            {
                $back = '';

                if(isset($colors[1]))
                    $back = array_key_exists($colors[0], static::$colors) ? ',' . static::$colors[$colors[1]] : '';

                $build[] = static::$char . static::$colors[$colors[0]] . $back . $data[1];
            }
            else if(array_key_exists($colors[0], static::$fontType))
            {
                $build[] = static::$fontType[$colors[0]] . $data[1];
            }
            else if(isset($data[1]))
            {
                $build[] = $data[1];
            }
        }

        return implode('', $build);
    }

    /**
     * @param $text
     * @return string
     */
    public static function rainbow($text)
    {
        $rainbow = [
            static::$colors['red'],
            (isset(static::$colors['orange']) ? static::$colors['orange'] : static::$colors['white']),
            static::$colors['yellow'],
            static::$colors['green'],
            static::$colors['cyan'],
            static::$colors['blue'],
            static::$colors['purple'],
        ];

        $chars  = str_split($text);
        $build  = '';
        $ci     = 0;

        for($i = 0; $i < count($chars); $i++)
        {
            $build .= static::$char . $rainbow[$ci] . $chars[$i];

            $ci++;

            if($ci > 6)
                $ci = 0;
        }

        return $build;
    }

    /**
     * @param $text
     * @return string
     */
    public static function random($text)
    {
        $chars  = str_split($text);
        $build  = '';
        $ci     = 0;
        $colors = array_values(static::$colors);
        $cc     = count($colors) - 1;

        shuffle($colors);

        for($i = 0; $i < count($chars); $i++)
        {
            $build .= static::$char . $colors[$ci] . $chars[$i];

            $ci++;

            if($ci > $cc)
                $ci = 0;
        }

        return $build;
    }
}