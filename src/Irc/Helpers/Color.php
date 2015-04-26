<?php namespace Dan\Irc\Helpers;


use Dan\Helpers\ColorParser;

class Color extends ColorParser {

    protected static $resetChar = "\x03";
    protected static $char      = "\x03";

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
}