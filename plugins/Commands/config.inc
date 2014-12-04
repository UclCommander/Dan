<?php

return [
    'files'  => [
        'CommandInterface.php', //this needs to load BEFORE commands
        'Command/Config.php',
        'Command/Hash.php',
        'Command/Join.php',
        'Command/Memory.php',
        'Command/Part.php',
        'Command/Ping.php',
        'Command/Plugin.php',
        'Command/Say.php',

        'CommandManager.php',
        'Commands.php', //this loads __LAST___
    ]
];