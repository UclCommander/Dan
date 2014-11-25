<?php

return [
    /*
    * Debug mode -- shows massive amounts of information
    */
    'debug' => true,

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
    'plugins' => [
        'commands',
        'title',
        'youtube',
        'fun',
    ]
];