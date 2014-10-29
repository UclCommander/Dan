<?php

return [
    'server'    => getenv('IRC_SERVER'),
    'port'      => getenv('IRC_PORT'),

    'username'  => getenv('IRC_USERNAME'),
    'nickname'  => getenv('IRC_NICKNAME'),
    'realname'  => getenv('IRC_REALNAME'),
    'password'  => getenv('IRC_PASSWORD'),

    'channels'   => explode('|', getenv('IRC_CHANNELS')),
];
