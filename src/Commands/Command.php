<?php namespace Dan\Commands; 

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

abstract class Command {

    public abstract function run(Channel $channel, User $user, $message);
    public abstract function help(User $user, $message);
}