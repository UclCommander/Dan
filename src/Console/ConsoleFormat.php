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
    ];

    protected static $char = "\x1B";
}