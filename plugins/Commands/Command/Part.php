<?php namespace Plugins\Commands\Command;

use Dan\Contracts\CommandContract;
use Dan\Core\Dan;
use Dan\Irc\Location\Channel;
use Dan\Irc\Location\User;


class Part implements CommandContract {

    /**
     * @inheritdoc
     */
    public function run(Channel $channel, User $user, $message)
    {
        $cmd        = explode(' ', $message, 2);
        $partFrom   = $cmd[0];
        $msg        = @$cmd[1];

        if(!in_array(substr($cmd[0], 0, 1), Support::get('CHANTYPES')))
        {
            $partFrom = $channel->getName();
            $msg = $cmd[0];
        }

        Dan::service('irc')->partChannel($partFrom, $msg);
    }


    /**
     * @inheritdoc
     */
    public function help(User $user, $message)
    {
        $user->sendNotice("part - Parts the current channel");
        $user->sendNotice("part [message] - Parts the current channel with an optional [message]");
        $user->sendNotice("part [channel] [message] - Parts [channel] with an optional [message]");
    }
}