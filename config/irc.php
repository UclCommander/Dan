<?php

return [
    /*
     * Server information.
     *
     * 'server' is the IRC server, aka irc.geekshed.net
     * 'port' is the server connection port, aka 6667
     * - DOES NOT SUPPORT SSL YET -
     */
    'server'    => getenv('IRC_SERVER'),
    'port'      => getenv('IRC_PORT'),

    /*
     * User information.
     */
    'username'  => getenv('IRC_USERNAME'),
    'nickname'  => getenv('IRC_NICKNAME'),
    'realname'  => getenv('IRC_REALNAME'),
    'password'  => getenv('IRC_PASSWORD'),

    /*
     * Auto join channels.
     */
    'channels'   => explode('|', getenv('IRC_CHANNELS')),


    /*
     * NickServ authentication command.
     * Default should work on most IRC servers.
     */
    'nickserv_auth_command' => "PRIVMSG NickServ IDENTIFY %s",
];
