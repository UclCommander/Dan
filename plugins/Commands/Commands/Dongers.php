<?php namespace Plugins\Commands\Commands;

use Dan\Commands\Command;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Dongers extends Command {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage('ヽ༼ຈل͜ຈ༽ﾉ raise your dongers ヽ༼ຈل͜ຈ༽ﾉ');
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("RAISES THE DONGERS");
    }
}