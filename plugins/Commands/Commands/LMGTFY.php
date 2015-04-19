<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Helpers\Linky;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class LMGTFY extends Command {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $link = Linky::fetchLink("http://lmgtfy.com/?q=" . urlencode($message));
        $channel->sendMessage("{reset}[{cyan} {$link} {reset}]");
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("ding - Says Dong!");
    }
}