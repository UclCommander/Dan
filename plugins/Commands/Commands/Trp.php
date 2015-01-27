<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Trp extends Command {

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