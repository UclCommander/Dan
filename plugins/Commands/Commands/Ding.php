<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Ding extends Command {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage('Dong!');
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("ding - Says Dong!");
    }
}