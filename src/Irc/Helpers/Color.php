<?php namespace Dan\Irc\Helpers;


class Color {

    protected static $resetChar = "\x03";

    protected static $colors = [
        'white'         => "00",
        'black'         => "01",
        'blue'          => "02",
        'green'         => "03",
        'red'           => "04",
        'maroon'        => "05",
        'purple'        => "06",
        'orange'        => "07",
        'yellow'        => "08",
        'light_green'   => "09",
        'cyan'          => "10",
        'light_cyan'    => "11",
        'light_blue'    => "12",
        'pink'          => "13",
        'gray'          => "14",
        'light_gray'    => "15",
    ];

    protected static $fontType = [
        'bold'      => "\x02",
        'b'         => "\x02",
        'underline' => "\x1F",
        'u'         => "\x1F",
        'italic'    => "\x16",
        'i'         => "\x16",
        'normal'    => "\x0F",
        'r'         => "\x0F",
    ];

    /**
     * @param $text
     * @return mixed
     */
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

                $build = static::$resetChar . $color . ($background != null ? ',' . $background : '');
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