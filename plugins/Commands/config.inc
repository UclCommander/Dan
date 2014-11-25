<?php

return [
    'files'  => [
        'CommandInterface.php', //this needs to load BEFORE commands
        'Command/Config.php',
        'Command/Hash.php',
        'Command/Join.php',
        'Command/Part.php',
        'Command/Ping.php',
        'Command/Plugin.php',

        'CommandManager.php',
        'Commands.php', //this loads __LAST___
    ]
];