<?php namespace Dan\Contracts;


use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

interface CommandContract {

    public function run(Channel $channel, User $user, $message);
    public function help();
}