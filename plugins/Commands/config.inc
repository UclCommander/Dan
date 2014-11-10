<?php

return [
    'files'  => [
        'CommandInterface.php', //this needs to load BEFORE commands

        'Command/Config.php',
        'Command/Hash.php',
        'Command/Join.php',
        'Command/Lenny.php',
        'Command/Part.php',
        'Command/Ping.php',
        'Command/Plugin.php',

        'Commands.php', //this loads __LAST___
    ]
];