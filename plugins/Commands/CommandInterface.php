<?php namespace Plugins\Commands;

use Dan\Irc\Channel;
use Dan\Irc\User;

interface CommandInterface {
    public function run(Channel $channel, User $user, $message);
} 