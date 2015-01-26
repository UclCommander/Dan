<?php namespace Plugins\Fun\Commands;

use Dan\Contracts\CommandContract;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Nbc implements CommandContract {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage("http://youtu.be/6qLz1L9YqIs");
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("Need help? Nobody cares.");
    }
}