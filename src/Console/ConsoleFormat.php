<?php namespace Dan\Console;


use Dan\Helpers\ColorParser;

class ConsoleFormat extends ColorParser {

    protected static $resetChar = "\x1B[39m";

    protected static $colors = [
        'black'         => "[30m",
        'red'           => "[31m",
        'green'         => "[32m",
        'yellow'        => "[33m",
        'blue'          => "[34m",
        'purple'        => "[35m",
        'cyan'          => "[36m",
        'white'         => "[37m",

        'backBlack'     => "[40m",
        'backRed'       => "[41m",
        'backGreen'     => "[42m",
        'backYellow'    => "[43m",
        'backBlue'      => "[44m",
        'backMagenta'   => "[45m",
        'backCyan'      => "[46m",
        'backLightGray' => "[47m",
        'backDefault'   => "[49m",
    ];

    protected static $char = "\x1B";
}