<?php namespace Plugins\Fun\Commands;

use Dan\Contracts\CommandContract;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;

class Lenny implements CommandContract {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $channel->sendMessage("( ͡° ͜ʖ ͡°)");
    }

    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("Give me da booty ( ͡° ͜ʖ ͡°)");
    }
}