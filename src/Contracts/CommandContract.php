<?php namespace Dan\Contracts;

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

interface CommandContract {

    /**
     * Runs the command.
     *
     * @param \Dan\Irc\Location\Channel  $channel
     * @param \Dan\Irc\Location\User  $user
     * @param string  $message
     */
    public function run(Channel $channel, User $user, $message);

    /**
     * Command help.
     *
     * @param \Dan\Irc\Location\User  $user
     * @param  string $message
     * @return mixed
     */
    public function help(User $user, $message);
}