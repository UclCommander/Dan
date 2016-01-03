<?php

namespace Dan\Contracts;

use Dan\Irc\Connection;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

interface CommandContract
{
    public function run(Connection $connection, User $user, Channel $channel, array $data);
}
