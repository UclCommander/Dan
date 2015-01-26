<?php namespace Plugins\Fun\Commands;

use Dan\Contracts\CommandContract;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Trp implements CommandContract {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage("popcorn anyone? http://puu.sh/4JYlr/0b652af25d.jpg");
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("Gives popcorn.");
    }
}