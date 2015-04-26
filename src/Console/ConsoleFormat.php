<?php namespace Dan\Console;


use Dan\Helpers\ColorParser;

class ConsoleFormat extends ColorParser {

    protected static $reset    = "\x1B[0m";

    protected static $colors = [
        'black'         => "[30m",
        'red'           => "[31m",
        'green'         => "[32m",
        'yellow'        => "[33m",
        'blue'          => "[34m",
        'purple'        => "[35m",
        'cyan'          => "[36m",
        'white'         => "[37m",

        'darkGray'      => "[30m",
        'lightBlue'     => "[34m",
        'lightGreen'    => "[32m",
        'lightCyan'     => "[36m",
        'lightRed'      => "[31m",
        'lightPurple'   => "[35m",
        'brown'         => "[33m",
        'lightGray'     => "[37m",

        'backBlack'     => "[040",
        'backRed'       => "[041",
        'backGreen'     => "[042",
        'backYellow'    => "[043",
        'backBlue'      => "[044",
        'backMagenta'   => "[045",
        'backCyan'      => "[046",
        'backLightGray' => "[047",
    ];

    protected static $char = "\x1B";
}