<?php

namespace Dan\Console;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class OutputStyle extends SymfonyStyle
{
    public function __construct($input, $output)
    {
        parent::__construct($input, $output);

        $this->setFormatter(new OutputFormatter(true, [
            'error'     => new OutputFormatterStyle('red'),
            'info'      => new OutputFormatterStyle('cyan'),
            'warning'   => new OutputFormatterStyle('yellow'),
            'debug'     => new OutputFormatterStyle('magenta'),
            'success'   => new OutputFormatterStyle('green'),

            'black'         => new OutputFormatterStyle('black'),
            'red'           => new OutputFormatterStyle('red'),
            'green'         => new OutputFormatterStyle('green'),
            'yellow'        => new OutputFormatterStyle('yellow'),
            'orange'        => new OutputFormatterStyle('yellow'),
            'blue'          => new OutputFormatterStyle('blue'),
            'magenta'       => new OutputFormatterStyle('magenta'),
            'cyan'          => new OutputFormatterStyle('cyan'),
            'light_cyan'    => new OutputFormatterStyle('cyan'),
            'white'         => new OutputFormatterStyle('white'),
            'default'       => new OutputFormatterStyle('default'),
        ]));
    }
}
