<?php

return [
    /*
    * Debug mode -- shows massive amounts of information
    */
    'debug' => getenv('DEBUG'),

    /*
     * Sudo users. These users have special permissions
     * Format: nick!user@host
     * Accepts wildcards.
     */
    'sudo_users'    => [
        'UclCommander!~UclComman@uclcommander.net',
    ],

    /*
     * The plugins to autoload.
     */
    'plugins' => explode('|', getenv("PLUGINS")),
];