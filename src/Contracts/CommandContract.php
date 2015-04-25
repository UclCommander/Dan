<?php namespace Dan\Contracts;

use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

interface CommandContract {

    /**
     * The public message handler.
     *
     * @param \Dan\Irc\Location\Channel $channel
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function run(Channel $channel, User $user, $message);
    
    /**
     * Help entry point.
     *
     * @param \Dan\Irc\Location\User $user
     * @param string $message
     */
    public function help(User $user, $message);

    /**
     * Gets the commands name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the default rank.
     *
     * @return string
     */
    public function getDefaultRank();
}